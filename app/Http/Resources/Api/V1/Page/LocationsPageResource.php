<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class LocationsPageResource extends ApiResource
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
            ],
            'us' => [
                'hero' => [
                    'eyebrow' => $this->us_hero_eyebrow,
                    'title' => $this->us_hero_title,
                    'description' => $this->us_hero_description,
                    'image' => $this->image($this->us_hero_image),
                ],
                'locations' => [
                    'title' => $this->us_locations_title,
                    'description' => $this->us_locations_description,
                ],
            ],
        ];
    }
}
