<?php

namespace App\Support\Images;

use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Throwable;

class ImageUploadPipeline
{
    public function store(BaseFileUpload $component, TemporaryUploadedFile $file): ?string
    {
        if (! $this->fileExists($file)) {
            return null;
        }

        if (! $this->isImageUpload($file)) {
            return $this->storeDefault($component, $file);
        }

        if (! $this->shouldTransform($file)) {
            return $this->storeDefault($component, $file);
        }

        return $this->storeTransformedImage($component, $file);
    }

    public function delete(BaseFileUpload $component, string $file): void
    {
        $component->getDisk()->delete($file);

        $directory = trim(pathinfo($file, PATHINFO_DIRNAME), './');
        $baseName = pathinfo($file, PATHINFO_FILENAME);

        $this->deleteLegacyVariants($component, $directory, $baseName);
    }

    protected function fileExists(TemporaryUploadedFile $file): bool
    {
        try {
            return $file->exists();
        } catch (Throwable) {
            return false;
        }
    }

    protected function isImageUpload(TemporaryUploadedFile $file): bool
    {
        return str_starts_with((string) $file->getMimeType(), 'image/');
    }

    protected function shouldTransform(TemporaryUploadedFile $file): bool
    {
        return in_array(strtolower((string) $file->getMimeType()), [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
        ], true);
    }

    protected function storeDefault(BaseFileUpload $component, TemporaryUploadedFile $file): ?string
    {
        $path = $file->storeAs(
            $component->getDirectory(),
            $component->getUploadedFileNameForStorage($file),
            $component->getDiskName(),
        );

        if ($component->getVisibility() === 'public') {
            rescue(fn () => $component->getDisk()->setVisibility($path, 'public'), report: false);
        }

        return $path;
    }

    protected function storeTransformedImage(BaseFileUpload $component, TemporaryUploadedFile $file): string
    {
        $storageFileName = $component->getUploadedFileNameForStorage($file);
        $directory = trim((string) $component->getDirectory(), '/');
        $baseName = pathinfo($storageFileName, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($storageFileName, PATHINFO_EXTENSION));
        $mimeType = strtolower((string) $file->getMimeType());

        $mainPath = $this->joinPath($directory, "{$baseName}.{$extension}");
        $this->storeMainVariant($component, $file, $mainPath, $mimeType);

        return $mainPath;
    }

    protected function storeMainVariant(BaseFileUpload $component, TemporaryUploadedFile $file, string $path, string $mimeType): void
    {
        $image = Image::decodePath($file->getRealPath())
            ->orient()
            ->scaleDown(width: (int) config('image_pipeline.main_width'));

        $temporaryPath = $this->writeEncodedImageToTemporaryFile($image, $mimeType);

        $this->optimize($temporaryPath);
        $this->storeTemporaryFileOnDisk($component, $temporaryPath, $path);
    }

    protected function writeEncodedImageToTemporaryFile(mixed $image, string $mimeType): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'img-pipeline-');

        $encodedImage = match ($mimeType) {
            'image/jpeg', 'image/jpg' => $image->encodeUsingMediaType(
                'image/jpeg',
                progressive: true,
                quality: (int) config('image_pipeline.jpeg_quality'),
            ),
            'image/png' => $image->encodeUsingMediaType('image/png'),
            default => $image->encodeUsingMediaType('image/webp', quality: (int) config('image_pipeline.webp_quality')),
        };

        file_put_contents($temporaryPath, (string) $encodedImage);

        return $temporaryPath;
    }

    protected function optimize(string $path): void
    {
        rescue(
            fn () => ImageOptimizer::optimize($path),
            report: false,
        );
    }

    protected function storeTemporaryFileOnDisk(BaseFileUpload $component, string $temporaryPath, string $destinationPath): void
    {
        $stream = fopen($temporaryPath, 'r');

        $component->getDisk()->put($destinationPath, $stream, $component->getVisibility());

        if (is_resource($stream)) {
            fclose($stream);
        }

        File::delete($temporaryPath);
    }

    protected function joinPath(string $directory, string $path): string
    {
        return trim(collect([$directory, $path])->filter()->implode('/'), '/');
    }

    protected function deleteLegacyVariants(BaseFileUpload $component, string $directory, string $baseName): void
    {
        $legacyVariantsDirectory = $this->joinPath($directory, 'variants');

        rescue(function () use ($component, $legacyVariantsDirectory, $baseName): void {
            collect($component->getDisk()->files($legacyVariantsDirectory))
                ->filter(fn (string $path): bool => str_starts_with(pathinfo($path, PATHINFO_FILENAME), "{$baseName}-"))
                ->each(fn (string $path): bool => $component->getDisk()->delete($path));
        }, report: false);
    }
}
