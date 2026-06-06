<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaginatedIndexRequest;
use App\Http\Resources\Api\V1\TreatmentResource;
use App\Models\Treatment;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TreatmentController extends Controller
{
    public function index(PaginatedIndexRequest $request): AnonymousResourceCollection
    {
        $treatments = Treatment::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->paginate($request->perPage())
            ->withQueryString();

        return TreatmentResource::collection($treatments);
    }
}
