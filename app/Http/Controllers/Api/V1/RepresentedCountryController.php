<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RepresentedCountryResource;
use App\Models\RepresentedCountry;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RepresentedCountryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RepresentedCountryResource::collection(
            RepresentedCountry::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
        );
    }
}
