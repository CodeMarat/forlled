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

        $temporaryPath = $this->transcodeVideo($sourceDisk->path($this->path));

        if ($temporaryPath === null) {
            return;
        }

        $optimizedPath = $this->optimizedPath($this->path);

        $stream = fopen($temporaryPath, 'r');

        $stored = $sourceDisk->put($optimizedPath, $stream, [
            'visibility' => 'public',
        ]);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! $stored) {
            File::delete($temporaryPath);

            return;
        }

        File::delete($temporaryPath);
        $sourceDisk->delete($this->path);

        if ((string) data_get($model, $this->attribute) === $this->path) {
            $model->forceFill([
                $this->attribute => $optimizedPath,
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

    protected function optimizedPath(string $path): string
    {
        $directory = trim(pathinfo($path, PATHINFO_DIRNAME), './');
        $baseName = pathinfo($path, PATHINFO_FILENAME);

        return trim(collect([$directory, "{$baseName}.mp4"])->filter()->implode('/'), '/');
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

    protected function transcodeVideo(string $inputPath): ?string
    {
        $ffmpeg = $this->binaryPath('ffmpeg');

        if ($ffmpeg === null) {
            return null;
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'video-pipeline-');

        if ($temporaryPath === false) {
            return null;
        }

        $outputPath = $temporaryPath.'.mp4';
        File::delete($temporaryPath);

        $command = sprintf(
            '%s -y -i %s -vf %s -c:v libx264 -preset slow -crf %d -pix_fmt yuv420p -movflags +faststart -c:a aac -b:a %s -ar 48000 %s',
            escapeshellarg($ffmpeg),
            escapeshellarg($inputPath),
            escapeshellarg(sprintf("scale='min(%d,iw)':-2", (int) config('image_pipeline.video_max_width'))),
            (int) config('image_pipeline.video_crf'),
            (string) config('image_pipeline.video_audio_bitrate'),
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
