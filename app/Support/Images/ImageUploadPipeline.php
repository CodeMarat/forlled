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

        if ($this->isImageUpload($file)) {
            try {
                return $this->storeOptimizedImage($component, $file);
            } catch (Throwable $exception) {
                report($exception);

                return $this->storeDefault($component, $file);
            }
        }

        if ($this->isVideoUpload($file)) {
            try {
                return $this->storeRawVideo($component, $file);
            } catch (Throwable $exception) {
                report($exception);

                return $this->storeDefault($component, $file);
            }
        }

        return $this->storeDefault($component, $file);
    }

    public function delete(BaseFileUpload $component, string $file): void
    {
        app(UploadedFileCleanup::class)->deletePathOnDisk($component->getDiskName(), $file);
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

    protected function isVideoUpload(TemporaryUploadedFile $file): bool
    {
        return str_starts_with((string) $file->getMimeType(), 'video/');
    }

    protected function shouldOptimizeImage(TemporaryUploadedFile $file): bool
    {
        return in_array(strtolower((string) $file->getMimeType()), [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
            'image/avif',
            'image/heic',
            'image/heif',
            'image/bmp',
            'image/tiff',
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
            rescue(fn (): bool => $component->getDisk()->setVisibility($path, 'public'), report: false);
        }

        return $path;
    }

    protected function storeOptimizedImage(BaseFileUpload $component, TemporaryUploadedFile $file): string
    {
        if (! $this->shouldOptimizeImage($file)) {
            return $this->storeDefault($component, $file);
        }

        $storageFileName = $component->getUploadedFileNameForStorage($file);
        $directory = trim((string) $component->getDirectory(), '/');
        $baseName = pathinfo($storageFileName, PATHINFO_FILENAME);
        $destinationPath = $this->joinPath($directory, "{$baseName}.webp");

        $temporaryPath = $this->encodeImageToTemporaryFile($file);
        $this->optimize($temporaryPath);
        $this->storeTemporaryFileOnDisk($component, $temporaryPath, $destinationPath);

        return $destinationPath;
    }

    protected function encodeImageToTemporaryFile(TemporaryUploadedFile $file): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'img-pipeline-');

        if ($temporaryPath === false) {
            throw new \RuntimeException('Unable to create a temporary file for image optimization.');
        }

        $image = Image::decodePath($file->getRealPath())
            ->orient()
            ->scaleDown(width: (int) config('image_pipeline.main_width'));

        $encodedImage = $image->encodeUsingMediaType(
            'image/webp',
            quality: (int) config('image_pipeline.webp_quality'),
            strip: true,
        );

        file_put_contents($temporaryPath, (string) $encodedImage);

        return $temporaryPath;
    }

    protected function storeRawVideo(BaseFileUpload $component, TemporaryUploadedFile $file): string
    {
        return $this->storeDefault($component, $file);
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

    protected function optimize(string $path): void
    {
        rescue(
            fn () => ImageOptimizer::optimize($path),
            report: false,
        );
    }

    protected function joinPath(string $directory, string $path): string
    {
        return trim(collect([$directory, $path])->filter()->implode('/'), '/');
    }
}
