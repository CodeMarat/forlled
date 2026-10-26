<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\PrivacyPolicyPageResource;
use App\Models\PrivacyPolicyPage;

class PrivacyPolicyPageController extends Controller
{
    public function __invoke(): PrivacyPolicyPageResource
    {
        return PrivacyPolicyPageResource::make(PrivacyPolicyPage::query()->firstOrFail());
    }
}
