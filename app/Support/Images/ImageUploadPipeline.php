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

        try {
            return $this->storeTransformedImage($component, $file);
        } catch (Throwable $exception) {
            report($exception);

            return $this->storeDefault($component, $file);
        }
    }

    public function delete(BaseFileUpload $component, string $file): void
    {
        $component->getDisk()->delete($file);

        $directory = trim(pathinfo($file, PATHINFO_DIRNAME), './');
        $baseName = pathinfo($file, PATHINFO_FILENAME);

        $this->deleteGeneratedVariants($component, $directory, $baseName);
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

        $this->deleteGeneratedVariants($component, $directory, $baseName);
        $this->storeMainVariant($component, $file, $mainPath, $mimeType);
        $this->storeVariants($component, $file, $directory, $baseName, $extension, $mimeType);

        return $mainPath;
    }

    protected function storeMainVariant(BaseFileUpload $component, TemporaryUploadedFile $file, string $path, string $mimeType): void
    {
        $image = $this->createImage($file)
            ->scaleDown(width: (int) config('image_pipeline.main_width'));

        $temporaryPath = $this->writeEncodedImageToTemporaryFile($image, $mimeType);

        $this->optimize($temporaryPath);
        $this->storeTemporaryFileOnDisk($component, $temporaryPath, $path);
    }

    protected function storeVariants(
        BaseFileUpload $component,
        TemporaryUploadedFile $file,
        string $directory,
        string $baseName,
        string $extension,
        string $mimeType,
    ): void {
        /** @var array<string, array<string, mixed>> $variants */
        $variants = config('image_pipeline.variants', []);

        foreach ($variants as $variantName => $variantConfig) {
            $variantMimeType = $this->variantMimeType($mimeType, $variantConfig);
            $variantExtension = $this->variantExtension($extension, $variantMimeType);
            $variantPath = $this->variantPath($directory, $baseName, $variantName, $variantExtension);

            $image = $this->createImage($file)
                ->scaleDown(width: (int) $variantConfig['width']);

            $temporaryPath = $this->writeEncodedImageToTemporaryFile(
                $image,
                $variantMimeType,
                $variantConfig,
            );

            $this->optimize($temporaryPath);
            $this->storeTemporaryFileOnDisk($component, $temporaryPath, $variantPath);
        }
    }

    protected function createImage(TemporaryUploadedFile $file): mixed
    {
        return Image::decodePath($file->getRealPath())->orient();
    }

    /**
     * @param  array<string, mixed>  $variantConfig
     */
    protected function writeEncodedImageToTemporaryFile(mixed $image, string $mimeType, array $variantConfig = []): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'img-pipeline-');

        $encodedImage = match ($mimeType) {
            'image/jpeg', 'image/jpg' => $image->encodeUsingMediaType(
                'image/jpeg',
                progressive: true,
                quality: (int) ($variantConfig['jpeg_quality'] ?? config('image_pipeline.jpeg_quality')),
            ),
            'image/png' => $image->encodeUsingMediaType('image/png'),
            default => $image->encodeUsingMediaType(
                'image/webp',
                quality: (int) ($variantConfig['quality'] ?? $variantConfig['webp_quality'] ?? config('image_pipeline.webp_quality')),
            ),
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

    protected function deleteGeneratedVariants(BaseFileUpload $component, string $directory, string $baseName): void
    {
        $variantsDirectory = $this->joinPath($directory, (string) config('image_pipeline.variants_directory'));

        rescue(function () use ($component, $variantsDirectory, $baseName): void {
            collect($component->getDisk()->files($variantsDirectory))
                ->filter(fn (string $path): bool => str_starts_with(pathinfo($path, PATHINFO_FILENAME), "{$baseName}-"))
                ->each(fn (string $path): bool => $component->getDisk()->delete($path));
        }, report: false);
    }

    /**
     * @param  array<string, mixed>  $variantConfig
     */
    protected function variantMimeType(string $sourceMimeType, array $variantConfig): string
    {
        if (($variantConfig['format'] ?? 'source') === 'webp') {
            return 'image/webp';
        }

        return $sourceMimeType;
    }

    protected function variantExtension(string $sourceExtension, string $variantMimeType): string
    {
        return match ($variantMimeType) {
            'image/jpeg', 'image/jpg' => in_array($sourceExtension, ['jpeg', 'jpg'], true) ? $sourceExtension : 'jpg',
            'image/png' => 'png',
            default => 'webp',
        };
    }

    protected function variantPath(string $directory, string $baseName, string $variantName, string $extension): string
    {
        return $this->joinPath(
            $directory,
            trim((string) config('image_pipeline.variants_directory'), '/')."/{$baseName}-{$variantName}.{$extension}",
        );
    }
}
