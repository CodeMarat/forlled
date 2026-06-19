<?php

use App\Http\Controllers\Api\V1\BlogPostController;
use App\Http\Controllers\Api\V1\ContactUsRequestController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\Page\AboutUsPageController;
use App\Http\Controllers\Api\V1\Page\BecomePartnerPageController;
use App\Http\Controllers\Api\V1\Page\BlogPageController;
use App\Http\Controllers\Api\V1\Page\ContactUsPageController;
use App\Http\Controllers\Api\V1\Page\FeaturedInPageController;
use App\Http\Controllers\Api\V1\Page\HomePageController;
use App\Http\Controllers\Api\V1\Page\LocationsPageController;
use App\Http\Controllers\Api\V1\Page\SocialMediaPageController;
use App\Http\Controllers\Api\V1\Page\TechnologyPageController;
use App\Http\Controllers\Api\V1\Page\TreatmentPageController;
use App\Http\Controllers\Api\V1\PartnerRequestController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\TreatmentController;
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
                Route::get('featured-in', FeaturedInPageController::class)->name('featured-in');
                Route::get('blog', BlogPageController::class)->name('blog');
                Route::get('locations', LocationsPageController::class)->name('locations');
                Route::get('social-media', SocialMediaPageController::class)->name('social-media');
                Route::get('treatments', TreatmentPageController::class)->name('treatments');
            });

        Route::apiResource('blog-posts', BlogPostController::class)
            ->parameters(['blog-posts' => 'slug'])
            ->only(['index', 'show']);
        Route::apiResource('locations', LocationController::class)->only(['index']);
        Route::post('contact-us-requests', ContactUsRequestController::class)->name('contact-us-requests.store');
        Route::apiResource('products', ProductController::class)
            ->parameters(['products' => 'slug'])
            ->only(['index', 'show']);
        Route::apiResource('product-categories', ProductCategoryController::class)
            ->parameters(['product-categories' => 'slug'])
            ->only(['index', 'show']);
        Route::apiResource('treatments', TreatmentController::class)->only(['index']);
        Route::post('partner-requests', PartnerRequestController::class)->name('partner-requests.store');
    });
