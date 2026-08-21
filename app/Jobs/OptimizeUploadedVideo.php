<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        $temporaryDirectory = $this->transcodeVideoToHls($sourceDisk->path($this->path));

        if ($temporaryDirectory === null) {
            Log::warning('Video optimization job failed: ffmpeg transcode returned null.', [
                'path' => $this->path,
            ]);

            return;
        }

        $destinationDirectory = $this->destinationDirectory($this->path);
        $playlistPath = $this->playlistPath($destinationDirectory);

        if (! $this->storeTemporaryDirectoryOnDisk($sourceDisk, $temporaryDirectory, $destinationDirectory)) {
            Log::warning('Video optimization job failed: could not store HLS files.', [
                'destination_directory' => $destinationDirectory,
                'path' => $this->path,
            ]);

            File::deleteDirectory($temporaryDirectory);

            return;
        }

        File::deleteDirectory($temporaryDirectory);
        $sourceDisk->delete($this->path);

        if ((string) data_get($model, $this->attribute) === $this->path) {
            $model->forceFill([
                $this->attribute => $playlistPath,
            ])->saveQuietly();

            Log::info('Video optimization job completed.', [
                'model' => $this->modelClass,
                'key' => $this->modelKey,
                'attribute' => $this->attribute,
                'playlist' => $playlistPath,
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

    protected function destinationDirectory(string $path): string
    {
        $directory = trim(pathinfo($path, PATHINFO_DIRNAME), './');
        $baseName = pathinfo($path, PATHINFO_FILENAME);

        return trim(collect([$directory, $baseName])->filter()->implode('/'), '/');
    }

    protected function playlistPath(string $destinationDirectory): string
    {
        return trim($destinationDirectory.'/'.(string) config('image_pipeline.video_hls_playlist_name', 'master.m3u8'), '/');
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

    protected function transcodeVideoToHls(string $inputPath): ?string
    {
        $ffmpeg = $this->binaryPath('ffmpeg');

        if ($ffmpeg === null) {
            return null;
        }

        $temporaryDirectory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'hls-pipeline-'.bin2hex(random_bytes(8));

        if (! mkdir($temporaryDirectory, 0775, true) && ! is_dir($temporaryDirectory)) {
            return null;
        }

        $playlistPath = $temporaryDirectory.'/'.(string) config('image_pipeline.video_hls_playlist_name', 'master.m3u8');
        $segmentDirectory = $temporaryDirectory.'/'.(string) config('image_pipeline.video_hls_segment_directory', 'segments');

        if (! mkdir($segmentDirectory, 0775, true) && ! is_dir($segmentDirectory)) {
            File::deleteDirectory($temporaryDirectory);

            return null;
        }

        $command = [
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
            sprintf("scale='min(%d\\,iw)':-2", (int) config('image_pipeline.video_max_width')),
            '-c:v',
            'libx264',
            '-preset',
            (string) config('image_pipeline.video_preset', 'veryfast'),
            '-crf',
            (string) (int) config('image_pipeline.video_crf'),
            '-threads',
            (string) (int) config('image_pipeline.video_threads', 1),
            '-pix_fmt',
            'yuv420p',
            '-c:a',
            'aac',
            '-b:a',
            (string) config('image_pipeline.video_audio_bitrate'),
            '-ar',
            '48000',
            '-f',
            'hls',
            '-hls_time',
            (string) (int) config('image_pipeline.video_hls_segment_time', 6),
            '-hls_playlist_type',
            'vod',
            '-hls_segment_type',
            'mpegts',
            '-hls_flags',
            'independent_segments',
            '-hls_segment_filename',
            $segmentDirectory.'/segment_%03d.ts',
            $playlistPath,
        ];

        $result = Process::path(dirname($inputPath))
            ->forever()
            ->idleTimeout(3600)
            ->run($command);

        if (! $result->successful() || ! is_file($playlistPath)) {
            Log::warning('Video optimization job failed: ffmpeg command did not produce a playlist.', [
                'exit_code' => $result->exitCode(),
                'input_path' => $inputPath,
                'playlist_path' => $playlistPath,
                'stderr' => trim($result->errorOutput()),
                'stdout' => trim($result->output()),
                'command' => implode(' ', array_map(
                    static fn (string $argument): string => escapeshellarg($argument),
                    $command,
                )),
            ]);

            File::deleteDirectory($temporaryDirectory);

            return null;
        }

        return $temporaryDirectory;
    }

    protected function storeTemporaryDirectoryOnDisk($disk, string $temporaryDirectory, string $destinationDirectory): bool
    {
        $disk->makeDirectory($destinationDirectory);

        $files = collect(File::allFiles($temporaryDirectory));

        foreach ($files as $file) {
            $relativePath = ltrim(str_replace($temporaryDirectory, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $destinationPath = trim($destinationDirectory.'/'.$relativePath, '/');
            $stream = fopen($file->getPathname(), 'r');

            if (! is_resource($stream)) {
                $disk->deleteDirectory($destinationDirectory);

                return false;
            }

            $stored = $disk->put($destinationPath, $stream, [
                'visibility' => 'public',
            ]);

            fclose($stream);

            if (! $stored) {
                $disk->deleteDirectory($destinationDirectory);

                return false;
            }
        }

        if (! $disk->exists($this->playlistPath($destinationDirectory))) {
            $disk->deleteDirectory($destinationDirectory);

            return false;
        }

        return true;
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
