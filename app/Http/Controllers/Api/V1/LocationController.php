<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaginatedIndexRequest;
use App\Http\Resources\Api\V1\LocationResource;
use App\Models\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    public function index(PaginatedIndexRequest $request): AnonymousResourceCollection
    {
        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->paginate($request->perPage())
            ->withQueryString();

        return LocationResource::collection($locations);
    }
}
