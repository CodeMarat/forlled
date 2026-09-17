<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class TreatmentPageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'hero' => [
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
