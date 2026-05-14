<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class BlogPageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hero' => [
                'badge' => $this->hero_badge,
                'title' => $this->hero_title,
                'description' => $this->hero_description,
                'button' => [
                    'text' => $this->hero_button_text,
                    'url' => $this->hero_button_url,
                ],
                'image' => $this->image($this->hero_image),
            ],
        ];
    }
}
