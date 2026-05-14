<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\TechnologyPageResource;
use App\Models\TechnologyPage;

class TechnologyPageController extends Controller
{
    public function __invoke(): TechnologyPageResource
    {
        return TechnologyPageResource::make(TechnologyPage::query()->firstOrFail());
    }
}
