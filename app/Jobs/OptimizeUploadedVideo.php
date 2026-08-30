<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeUploadedVideo implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $modelClass,
        public string|int $modelKey,
        public string $attribute,
        public string $disk,
        public string $path,
    ) {
    }

    public function handle(): void
    {
        Log::info('Video optimization job started.', [
            'model' => $this->modelClass,
            'key' => $this->modelKey,
            'attribute' => $this->attribute,
            'path' => $this->path,
        ]);

        $model = $this->resolveModel();

        if (! $model instanceof Model) {
            Log::warning('Video optimization job skipped: model not found.', [
                'model' => $this->modelClass,
                'key' => $this->modelKey,
                'path' => $this->path,
            ]);

            $this->deleteSourceIfPresent();

            return;
        }

        if ((string) data_get($model, $this->attribute) !== $this->path) {
            Log::warning('Video optimization job skipped: path changed before processing.', [
                'model' => $this->modelClass,
                'key' => $this->modelKey,
                'attribute' => $this->attribute,
                'expected' => $this->path,
                'current' => data_get($model, $this->attribute),
            ]);

            return;
        }

        if (! $this->isProcessableVideoPath($this->path)) {
            Log::warning('Video optimization job skipped: unsupported path.', [
                'path' => $this->path,
            ]);

            return;
        }

        $sourceDisk = Storage::disk($this->disk);

        if (! $sourceDisk->exists($this->path)) {
            Log::warning('Video optimization job skipped: source file missing.', [
                'disk' => $this->disk,
                'path' => $this->path,
            ]);

            return;
        }

        $temporaryFile = $this->transcodeVideoToMp4($sourceDisk->path($this->path));

        if ($temporaryFile === null) {
            Log::warning('Video optimization job failed: ffmpeg transcode returned null.', [
                'path' => $this->path,
            ]);

            return;
        }

        $destinationPath = $this->destinationPath($this->path);

        if (! $this->storeTemporaryFileOnDisk($sourceDisk, $temporaryFile, $destinationPath)) {
            Log::warning('Video optimization job failed: could not store optimized video file.', [
                'destination_path' => $destinationPath,
                'path' => $this->path,
            ]);

            File::delete($temporaryFile);

            return;
        }

        File::delete($temporaryFile);
        $sourceDisk->delete($this->path);

        if ((string) data_get($model, $this->attribute) === $this->path) {
            $model->forceFill([
                $this->attribute => $destinationPath,
            ])->saveQuietly();

            Log::info('Video optimization job completed.', [
                'model' => $this->modelClass,
                'key' => $this->modelKey,
                'attribute' => $this->attribute,
                'path' => $destinationPath,
            ]);
        }
    }

    protected function resolveModel(): ?Model
    {
        $class = $this->modelClass;

        if (! is_a($class, Model::class, allow_string: true)) {
            return null;
        }

        return $class::query()->whereKey($this->modelKey)->first();
    }

    protected function deleteSourceIfPresent(): void
    {
        rescue(fn (): bool => Storage::disk($this->disk)->delete($this->path), report: false);
    }

    protected function destinationPath(string $path): string
    {
        $directory = trim(pathinfo($path, PATHINFO_DIRNAME), './');
        $baseName = pathinfo($path, PATHINFO_FILENAME);
        $extension = (string) config('image_pipeline.video_output_extension', 'mp4');

        return trim(collect([$directory, "{$baseName}.{$extension}"])->filter()->implode('/'), '/');
    }

    protected function isProcessableVideoPath(string $path): bool
    {
        $extensions = array_map(
            static fn (string $extension): string => preg_quote($extension, '/'),
            (array) config('image_pipeline.video_extensions', []),
        );

        if ($extensions === []) {
            return false;
        }

        return (bool) preg_match('/\.('.implode('|', $extensions).')$/i', $path);
    }

    protected function transcodeVideoToMp4(string $inputPath): ?string
    {
        $ffmpeg = $this->binaryPath('ffmpeg');

        if ($ffmpeg === null) {
            return null;
        }

        $temporaryBasePath = tempnam(sys_get_temp_dir(), 'video-pipeline-');

        if ($temporaryBasePath === false) {
            return null;
        }

        $outputPath = $temporaryBasePath.'.mp4';

        File::delete($temporaryBasePath);

        $command = $this->buildTranscodeCommand($ffmpeg, $inputPath, $outputPath);

        $result = Process::path(dirname($inputPath))
            ->forever()
            ->idleTimeout(3600)
            ->run($command);

        if (! $result->successful() || ! is_file($outputPath)) {
            Log::warning('Video optimization job failed: ffmpeg command did not produce an output file.', [
                'exit_code' => $result->exitCode(),
                'input_path' => $inputPath,
                'output_path' => $outputPath,
                'stderr' => trim($result->errorOutput()),
                'stdout' => trim($result->output()),
                'command' => implode(' ', array_map(
                    static fn (string $argument): string => escapeshellarg($argument),
                    $command,
                )),
            ]);

            File::delete($outputPath);

            return null;
        }

        return $outputPath;
    }

    /**
     * @return array<int, string>
     */
    protected function buildTranscodeCommand(string $ffmpeg, string $inputPath, string $outputPath): array
    {
        return [
            $ffmpeg,
            '-y',
            '-nostdin',
            '-hide_banner',
            '-loglevel',
            'error',
            '-i',
            $inputPath,
            '-map',
            '0:v:0',
            '-map',
            '0:a?',
            '-vf',
            sprintf('scale=min(%d\\,iw):-2', (int) config('image_pipeline.video_max_width', 960)),
            '-c:v',
            'libx264',
            '-preset',
            (string) config('image_pipeline.video_preset', 'ultrafast'),
            '-crf',
            (string) (int) config('image_pipeline.video_crf', 30),
            '-threads',
            (string) (int) config('image_pipeline.video_threads', 1),
            '-pix_fmt',
            'yuv420p',
            '-movflags',
            '+faststart',
            '-c:a',
            'aac',
            '-b:a',
            (string) config('image_pipeline.video_audio_bitrate', '64k'),
            '-ar',
            '48000',
            $outputPath,
        ];
    }

    protected function storeTemporaryFileOnDisk($disk, string $temporaryFile, string $destinationPath): bool
    {
        $directory = trim(pathinfo($destinationPath, PATHINFO_DIRNAME), './');

        if ($directory !== '') {
            $disk->makeDirectory($directory);
        }

        $stream = fopen($temporaryFile, 'r');

        if (! is_resource($stream)) {
            return false;
        }

        $stored = $disk->put($destinationPath, $stream, [
            'visibility' => 'public',
        ]);

        fclose($stream);

        return (bool) $stored && $disk->exists($destinationPath);
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
