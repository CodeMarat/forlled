<?php

namespace App\Jobs;

use Illuminate\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
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
        $model = $this->resolveModel();

        if (! $model instanceof Model) {
            $this->deleteSourceIfPresent();

            return;
        }

        if ((string) data_get($model, $this->attribute) !== $this->path) {
            return;
        }

        if (! $this->isProcessableVideoPath($this->path)) {
            return;
        }

        $sourceDisk = Storage::disk($this->disk);

        if (! $sourceDisk->exists($this->path)) {
            return;
        }

        $temporaryDirectory = $this->transcodeVideoToHls($sourceDisk->path($this->path));

        if ($temporaryDirectory === null) {
            return;
        }

        $destinationDirectory = $this->destinationDirectory($this->path);
        $playlistPath = $this->playlistPath($destinationDirectory);

        if (! $this->storeTemporaryDirectoryOnDisk($sourceDisk, $temporaryDirectory, $destinationDirectory)) {
            File::deleteDirectory($temporaryDirectory);

            return;
        }

        File::deleteDirectory($temporaryDirectory);
        $sourceDisk->delete($this->path);

        if ((string) data_get($model, $this->attribute) === $this->path) {
            $model->forceFill([
                $this->attribute => $playlistPath,
            ])->saveQuietly();
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

        $command = sprintf(
            '%s -y -i %s -map 0:v:0 -map 0:a? -vf %s -c:v libx264 -preset slow -crf %d -pix_fmt yuv420p -c:a aac -b:a %s -ar 48000 -f hls -hls_time %d -hls_playlist_type vod -hls_segment_type mpegts -hls_flags independent_segments -hls_segment_filename %s %s',
            escapeshellarg($ffmpeg),
            escapeshellarg($inputPath),
            escapeshellarg(sprintf("scale='min(%d,iw)':-2", (int) config('image_pipeline.video_max_width'))),
            (int) config('image_pipeline.video_crf'),
            (string) config('image_pipeline.video_audio_bitrate'),
            (int) config('image_pipeline.video_hls_segment_time', 6),
            escapeshellarg($segmentDirectory.'/segment_%03d.ts'),
            escapeshellarg($playlistPath),
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
            File::deleteDirectory($temporaryDirectory);

            return null;
        }

        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0 || ! is_file($playlistPath)) {
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
