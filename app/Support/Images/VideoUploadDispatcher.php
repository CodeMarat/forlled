<?php

namespace App\Support\Images;

use App\Jobs\OptimizeUploadedVideo;
use Illuminate\Database\Eloquent\Model;

class VideoUploadDispatcher
{
    public function dispatch(Model $model): void
    {
        $cleanup = app(UploadedFileCleanup::class);

        foreach ($model->getChanges() as $attribute => $value) {
            foreach ($cleanup->collectFilePaths($value) as $path) {
                if (! $this->isRawVideoPath($path)) {
                    continue;
                }

                OptimizeUploadedVideo::dispatch(
                    $model::class,
                    $model->getKey(),
                    $attribute,
                    'public',
                    $path,
                )->afterCommit();
            }
        }
    }

    protected function isRawVideoPath(string $path): bool
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
}
