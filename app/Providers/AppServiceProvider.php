<?php

namespace App\Providers;

use App\Support\Images\ImageUploadPipeline;
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
        FileUpload::configureUsing(function (FileUpload $component): void {
            $component
                ->maxSize((int) config('image_pipeline.max_upload_kb'));

            $component->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                return app(ImageUploadPipeline::class)->store($component, $file);
            });

            $component->deleteUploadedFileUsing(function (BaseFileUpload $component, string $file): void {
                app(ImageUploadPipeline::class)->delete($component, $file);
            });
        });
    }
}
