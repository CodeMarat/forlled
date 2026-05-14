<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\AboutUsPageResource;
use App\Models\AboutUs;

class AboutUsPageController extends Controller
{
    public function __invoke(): AboutUsPageResource
    {
        return AboutUsPageResource::make(AboutUs::query()->firstOrFail());
    }
}
