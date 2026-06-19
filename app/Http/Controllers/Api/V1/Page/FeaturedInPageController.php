<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\FeaturedInPageResource;
use App\Models\FeaturedInPage;

class FeaturedInPageController extends Controller
{
    public function __invoke(): FeaturedInPageResource
    {
        return FeaturedInPageResource::make(
            FeaturedInPage::query()->firstOrCreate(['id' => 1], [
                'title' => 'FEATURED IN',
                'logos' => [],
            ]),
        );
    }
}
