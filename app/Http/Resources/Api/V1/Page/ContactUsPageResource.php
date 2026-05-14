<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class ContactUsPageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
