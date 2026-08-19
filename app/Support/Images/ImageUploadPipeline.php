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
        $mimeType = strtolower((string) $file->getMimeType());
        $outputMimeType = $this->outputMimeType($mimeType);
        $outputExtension = $this->outputExtension($outputMimeType);

        $mainPath = $this->joinPath($directory, "{$baseName}.{$outputExtension}");

        $this->deleteGeneratedVariants($component, $directory, $baseName);
        $this->storeMainVariant($component, $file, $mainPath, $outputMimeType);
        $this->storeVariants($component, $file, $directory, $baseName, $outputMimeType);

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
        string $mimeType,
    ): void {
        /** @var array<string, array<string, mixed>> $variants */
        $variants = config('image_pipeline.variants', []);

        foreach ($variants as $variantName => $variantConfig) {
            $variantMimeType = $this->variantMimeType($mimeType, $variantConfig);
            $variantExtension = $this->outputExtension($variantMimeType);
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
                strip: true,
            ),
            'image/png' => $image->encodeUsingMediaType(
                'image/webp',
                quality: (int) ($variantConfig['quality'] ?? $variantConfig['webp_quality'] ?? config('image_pipeline.webp_quality')),
                strip: true,
            ),
            default => $image->encodeUsingMediaType(
                'image/webp',
                quality: (int) ($variantConfig['quality'] ?? $variantConfig['webp_quality'] ?? config('image_pipeline.webp_quality')),
                strip: true,
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

    /**
     * @param  array<string, mixed>  $variantConfig
     */
    protected function variantMimeType(string $sourceMimeType, array $variantConfig): string
    {
        if (! str_starts_with($sourceMimeType, 'image/') || in_array($sourceMimeType, ['image/gif', 'image/svg+xml'], true)) {
            return $sourceMimeType;
        }

        if (($variantConfig['format'] ?? 'source') === 'webp') {
            return 'image/webp';
        }

        return 'image/webp';
    }

    protected function outputMimeType(string $sourceMimeType): string
    {
        if (in_array($sourceMimeType, ['image/gif', 'image/svg+xml'], true)) {
            return $sourceMimeType;
        }

        return 'image/webp';
    }

    protected function outputExtension(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => 'jpg',
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
