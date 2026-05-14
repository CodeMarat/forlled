<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\BlogPageResource;
use App\Models\BlogPage;

class BlogPageController extends Controller
{
    public function __invoke(): BlogPageResource
    {
        return BlogPageResource::make(BlogPage::query()->firstOrFail());
    }
}
