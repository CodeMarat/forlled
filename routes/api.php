<?php

use App\Http\Controllers\Api\V1\BlogPostController;
use App\Http\Controllers\Api\V1\Page\AboutUsPageController;
use App\Http\Controllers\Api\V1\Page\BecomePartnerPageController;
use App\Http\Controllers\Api\V1\Page\BlogPageController;
use App\Http\Controllers\Api\V1\Page\ContactUsPageController;
use App\Http\Controllers\Api\V1\Page\HomePageController;
use App\Http\Controllers\Api\V1\Page\TechnologyPageController;
use App\Http\Controllers\Api\V1\PartnerRequestController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::prefix('pages')
            ->name('pages.')
            ->group(function (): void {
                Route::get('home', HomePageController::class)->name('home');
                Route::get('about-us', AboutUsPageController::class)->name('about-us');
                Route::get('technology', TechnologyPageController::class)->name('technology');
                Route::get('contact-us', ContactUsPageController::class)->name('contact-us');
                Route::get('become-partner', BecomePartnerPageController::class)->name('become-partner');
                Route::get('blog', BlogPageController::class)->name('blog');
            });

        Route::apiResource('blog-posts', BlogPostController::class)->only(['index', 'show']);
        Route::apiResource('products', ProductController::class)->only(['index', 'show']);
        Route::apiResource('product-categories', ProductCategoryController::class)->only(['index', 'show']);
        Route::post('partner-requests', PartnerRequestController::class)->name('partner-requests.store');
    });
