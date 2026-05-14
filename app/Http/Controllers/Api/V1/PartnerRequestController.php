<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartnerRequest;
use App\Models\PartnerRequest;
use Illuminate\Http\JsonResponse;

class PartnerRequestController extends Controller
{
    public function __invoke(StorePartnerRequest $request): JsonResponse
    {
        $partnerRequest = PartnerRequest::query()->create($request->validated());

        return response()->json([
            'message' => 'Partner request submitted successfully.',
            'id' => $partnerRequest->id,
        ], 201);
    }
}
