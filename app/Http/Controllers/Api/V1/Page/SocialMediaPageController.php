<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\SocialMediaPageResource;
use App\Models\SocialMedia;

class SocialMediaPageController extends Controller
{
    public function __invoke(): SocialMediaPageResource
    {
        return SocialMediaPageResource::make(
            SocialMedia::query()->firstOrCreate(['id' => 1]),
        );
    }
}
