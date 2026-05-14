<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\BecomePartnerPageResource;
use App\Models\BecomePartnerPage;

class BecomePartnerPageController extends Controller
{
    public function __invoke(): BecomePartnerPageResource
    {
        return BecomePartnerPageResource::make(BecomePartnerPage::query()->firstOrFail());
    }
}
