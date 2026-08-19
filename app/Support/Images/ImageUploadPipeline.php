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

        if ($this->isVideoUpload($file)) {
            return $this->storeVideo($component, $file);
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

    protected function isVideoUpload(TemporaryUploadedFile $file): bool
    {
        return str_starts_with((string) $file->getMimeType(), 'video/');
    }

    protected function shouldTransform(TemporaryUploadedFile $file): bool
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

        return $mainPath;
    }

    protected function storeVideo(BaseFileUpload $component, TemporaryUploadedFile $file): string
    {
        $transcodedPath = $this->transcodeVideo($file);

        if ($transcodedPath === null) {
            return $this->storeDefault($component, $file);
        }

        $storageFileName = $component->getUploadedFileNameForStorage($file);
        $directory = trim((string) $component->getDirectory(), '/');
        $baseName = pathinfo($storageFileName, PATHINFO_FILENAME);
        $destinationPath = $this->joinPath($directory, "{$baseName}.mp4");

        if ($this->fileIsSmallerThanOriginal($transcodedPath, $file->getRealPath())) {
            $this->storeTemporaryFileOnDisk($component, $transcodedPath, $destinationPath);

            return $destinationPath;
        }

        File::delete($transcodedPath);

        return $this->storeDefault($component, $file);
    }

    protected function storeMainVariant(BaseFileUpload $component, TemporaryUploadedFile $file, string $path, string $mimeType): void
    {
        $image = $this->createImage($file)
            ->scaleDown(width: (int) config('image_pipeline.main_width'));

        $temporaryPath = $this->writeOptimizedImageToTemporaryFile($image, $mimeType, $file->getRealPath());

        $this->optimize($temporaryPath);
        $this->storeTemporaryFileOnDisk($component, $temporaryPath, $path);
    }

    protected function createImage(TemporaryUploadedFile $file): mixed
    {
        return Image::decodePath($file->getRealPath())->orient();
    }

    protected function writeOptimizedImageToTemporaryFile(mixed $image, string $mimeType, string $originalPath): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'img-pipeline-');

        if ($temporaryPath === false) {
            throw new \RuntimeException('Unable to create a temporary file for image optimization.');
        }

        $qualityCandidates = $this->qualityCandidates();
        $bestPath = null;
        $bestSize = null;

        foreach ($qualityCandidates as $quality) {
            $candidatePath = $this->writeEncodedImageToTemporaryFile($image, $mimeType, $quality);
            $candidateSize = @filesize($candidatePath) ?: PHP_INT_MAX;

            if ($bestPath === null || $candidateSize < $bestSize) {
                if ($bestPath !== null) {
                    File::delete($bestPath);
                }

                $bestPath = $candidatePath;
                $bestSize = $candidateSize;
            } else {
                File::delete($candidatePath);
            }

            if ($candidateSize > 0 && @filesize($originalPath) !== false && $candidateSize < filesize($originalPath)) {
                break;
            }
        }

        if ($bestPath === null) {
            throw new \RuntimeException('Unable to optimize image.');
        }

        return $bestPath;
    }

    /**
     * @param  array<int, int>  $qualityCandidates
     */
    protected function qualityCandidates(): array
    {
        return [
            (int) config('image_pipeline.webp_quality'),
            90,
            85,
            80,
        ];
    }

    protected function writeEncodedImageToTemporaryFile(mixed $image, string $mimeType, int $quality): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'img-pipeline-');

        if ($temporaryPath === false) {
            throw new \RuntimeException('Unable to create a temporary file for image encoding.');
        }

        $encodedImage = match ($mimeType) {
            'image/jpeg', 'image/jpg' => $image->encodeUsingMediaType(
                'image/webp',
                progressive: true,
                quality: $quality,
                strip: true,
            ),
            default => $image->encodeUsingMediaType(
                'image/webp',
                quality: $quality,
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

    protected function transcodeVideo(TemporaryUploadedFile $file): ?string
    {
        if (! $this->hasFfmpeg()) {
            return null;
        }

        $inputPath = $file->getRealPath();
        $temporaryPath = tempnam(sys_get_temp_dir(), 'video-pipeline-');

        if ($temporaryPath === false) {
            return null;
        }

        $outputPath = $temporaryPath.'.mp4';
        File::delete($temporaryPath);

        $command = sprintf(
            '%s -y -i %s -vf %s -c:v libx264 -preset slow -crf 23 -pix_fmt yuv420p -movflags +faststart -c:a aac -b:a 128k -ar 48000 %s',
            $this->binaryPath('ffmpeg'),
            escapeshellarg($inputPath),
            escapeshellarg("scale='min(1920,iw)':-2"),
            escapeshellarg($outputPath),
        );

        $process = proc_open(
            $command,
            [
                ['pipe', 'r'],
                ['pipe', 'w'],
                ['pipe', 'w'],
            ],
            $pipes,
        );

        if (! is_resource($process)) {
            return null;
        }

        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0 || ! is_file($outputPath)) {
            File::delete($outputPath);

            return null;
        }

        return $outputPath;
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

    protected function fileIsSmallerThanOriginal(string $optimizedPath, string $originalPath): bool
    {
        return @filesize($optimizedPath) !== false
            && @filesize($originalPath) !== false
            && filesize($optimizedPath) < filesize($originalPath);
    }

    protected function joinPath(string $directory, string $path): string
    {
        return trim(collect([$directory, $path])->filter()->implode('/'), '/');
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
            default => 'webp',
        };
    }

    protected function hasFfmpeg(): bool
    {
        return $this->binaryPath('ffmpeg') !== null;
    }

    protected function binaryPath(string $binary): ?string
    {
        $paths = explode(PATH_SEPARATOR, (string) getenv('PATH'));

        foreach ($paths as $path) {
            $candidate = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$binary;

            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

}
