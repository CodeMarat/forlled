<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class SocialMediaPageResource extends ApiResource
{
    /**
     * @return array<string, string|null>
     */
    public function toArray(Request $request): array
    {
        return [
            'instagram_url' => $this->instagram_url,
            'facebook_url' => $this->facebook_url,
            'youtube_url' => $this->youtube_url,
            'linkedin_url' => $this->linkedin_url,
        ];
    }
}
