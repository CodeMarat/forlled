<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartnerRequest;
use App\Support\PartnerRequestSubmission;
use Illuminate\Http\JsonResponse;

class PartnerRequestController extends Controller
{
    public function store(StorePartnerRequest $request, PartnerRequestSubmission $submission): JsonResponse
    {
        $partnerRequest = $submission->submit($request->validated());

        return response()->json([
            'message' => 'Partner request submitted successfully.',
            'id' => $partnerRequest->id,
        ], 201);
    }
}
