<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class AboutUsPageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hero' => [
                'eyebrow' => $this->hero_eyebrow,
                'title' => $this->hero_title,
                'description' => $this->hero_description,
                'image' => $this->image($this->hero_image, alt: $this->hero_image_alt),
            ],
            'story' => [
                'title' => $this->story_title,
                'description' => $this->story_description,
                'secondary_text' => $this->story_secondary_text,
                'image' => $this->image($this->story_image, alt: $this->story_image_alt),
            ],
            'bottom' => [
                'description' => $this->bottom_description,
                'secondary_text' => $this->bottom_secondary_text,
                'image' => $this->image($this->bottom_image, alt: $this->bottom_image_alt),
            ],
        ];
    }
}
