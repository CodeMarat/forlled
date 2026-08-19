<?php

namespace App\Providers;

use App\Support\Images\UploadedFileCleanup;
use App\Support\Images\ImageUploadPipeline;
use App\Support\Images\VideoUploadDispatcher;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::updating(function (Model $model): void {
            app(UploadedFileCleanup::class)->rememberOriginalPaths($model);
        });

        Model::updated(function (Model $model): void {
            app(UploadedFileCleanup::class)->deleteRemovedFiles($model);
        });

        Model::saved(function (Model $model): void {
            app(VideoUploadDispatcher::class)->dispatch($model);
        });

        Model::deleted(function (Model $model): void {
            app(UploadedFileCleanup::class)->deleteModelFiles($model);
        });

        FileUpload::configureUsing(function (FileUpload $component): void {
            $component
                ->maxSize(fn (BaseFileUpload $component): ?int => array_intersect(
                    $component->getAcceptedFileTypes() ?? [],
                    ['image/*', 'video/*'],
                ) !== []
                    ? (int) config('image_pipeline.max_upload_kb')
                    : null);

            $component->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                return app(ImageUploadPipeline::class)->store($component, $file);
            });

            $component->deleteUploadedFileUsing(function (BaseFileUpload $component, string $file): void {
                app(ImageUploadPipeline::class)->delete($component, $file);
            });
        });
    }
}
