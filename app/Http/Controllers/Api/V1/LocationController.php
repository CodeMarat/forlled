<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LocationIndexRequest;
use App\Http\Resources\Api\V1\LocationResource;
use App\Models\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    public function index(LocationIndexRequest $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->validated('search', ''));
        $state = strtoupper((string) $request->validated('state', ''));

        $locations = Location::query()
            ->where('is_active', true)
            ->when($state !== '', fn ($query) => $query->where('state', $state))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('company', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%')
                        ->orWhere('state', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->paginate($request->perPage())
            ->withQueryString();

        return LocationResource::collection($locations);
    }
}
