<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\HomePageResource;
use App\Models\HomePage;

class HomePageController extends Controller
{
    public function __invoke(): HomePageResource
    {
        return HomePageResource::make(HomePage::query()->firstOrFail());
    }
}
