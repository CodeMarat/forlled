<?php

namespace App\Support\Images;

use App\Jobs\OptimizeUploadedVideo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VideoUploadDispatcher
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function dispatch(Model $model, array $attributes = []): void
    {
        $cleanup = app(UploadedFileCleanup::class);
        $modelClass = $model::class;
        $modelKey = $model->getKey();
        $attributes = $attributes !== [] ? $attributes : $model->getChanges();

        foreach ($attributes as $attribute => $value) {
            foreach ($cleanup->collectFilePaths($value) as $path) {
                if (! $this->isRawVideoPath($path)) {
                    continue;
                }

                $dispatch = static function () use ($modelClass, $modelKey, $attribute, $path): void {
                    OptimizeUploadedVideo::dispatch(
                        $modelClass,
                        $modelKey,
                        $attribute,
                        'public',
                        $path,
                    );
                };

                if (DB::transactionLevel() > 0) {
                    DB::afterCommit($dispatch);

                    continue;
                }

                $dispatch();
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
