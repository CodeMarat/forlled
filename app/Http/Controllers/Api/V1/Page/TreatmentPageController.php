<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\TreatmentPageResource;
use App\Models\TreatmentPage;
use Illuminate\Http\JsonResponse;

class TreatmentPageController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(
            TreatmentPageResource::make(TreatmentPage::query()->firstOrFail())->resolve(),
        );
    }
}
