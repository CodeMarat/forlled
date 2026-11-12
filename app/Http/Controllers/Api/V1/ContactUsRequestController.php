<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactUsRequest;
use App\Support\ContactUsRequestSubmission;
use Illuminate\Http\JsonResponse;

class ContactUsRequestController extends Controller
{
    public function __invoke(StoreContactUsRequest $request, ContactUsRequestSubmission $submission): JsonResponse
    {
        $contactUsRequest = $submission->submit($request->validated());

        return response()->json([
            'message' => 'Contact request submitted successfully.',
            'id' => $contactUsRequest->id,
        ], 201);
    }
}
