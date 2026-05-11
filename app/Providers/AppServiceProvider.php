<?php

namespace App\Providers;

use App\Support\Images\ImageUploadPipeline;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
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
        FilamentAsset::register([
            Js::make('filament-image-upload-optimizer', resource_path('js/filament-image-upload-optimizer.js')),
        ]);

        FilamentAsset::registerScriptData([
            'imageUploadOptimization' => [
                'clientResizeWidth' => (int) config('image_pipeline.client_resize_width'),
                'clientTransformQuality' => ((int) config('image_pipeline.client_transform_quality')) / 100,
            ],
        ]);

        FileUpload::configureUsing(function (FileUpload $component): void {
            $component
                ->automaticallyResizeImagesToWidth((string) config('image_pipeline.client_resize_width'))
                ->automaticallyUpscaleImagesWhenResizing(false);

            $component->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                return app(ImageUploadPipeline::class)->store($component, $file);
            });

            $component->deleteUploadedFileUsing(function (BaseFileUpload $component, string $file): void {
                app(ImageUploadPipeline::class)->delete($component, $file);
            });
        });
    }
}
