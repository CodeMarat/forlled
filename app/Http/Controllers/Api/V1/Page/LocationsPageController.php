<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\LocationsPageResource;
use App\Models\LocationsPage;
use Illuminate\Http\JsonResponse;

class LocationsPageController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(
            LocationsPageResource::make(LocationsPage::query()->firstOrFail())->resolve(),
        );
    }
}
