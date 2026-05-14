<?php

namespace App\Http\Controllers\Api\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Page\ContactUsPageResource;
use App\Models\ContactUs;

class ContactUsPageController extends Controller
{
    public function __invoke(): ContactUsPageResource
    {
        return ContactUsPageResource::make(ContactUs::query()->firstOrFail());
    }
}
