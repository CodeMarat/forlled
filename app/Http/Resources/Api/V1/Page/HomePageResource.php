<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class HomePageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $scienceGallery = array_values(array_filter([
            $this->image($this->gallery_image_1, alt: $this->gallery_image_1_alt),
            $this->image($this->gallery_image_2, alt: $this->gallery_image_2_alt),
            $this->image($this->gallery_image_3, alt: $this->gallery_image_3_alt),
            $this->image($this->gallery_image_4, alt: $this->gallery_image_4_alt),
        ]));

        return [
            'hero' => [
                'title' => $this->hero_title,
                'subtitle' => $this->hero_subtitle,
                'image' => $this->image($this->hero_image, alt: $this->hero_image_alt),
            ],
            'intro' => [
                'text' => $this->intro_text,
            ],
            'favorites' => [
                'title' => $this->favorites_title,
            ],
            'images_duo' => [
                'left' => [
                    'image' => $this->image($this->duo_left_image, alt: $this->duo_left_image_alt),
                    'caption' => $this->duo_left_caption,
                ],
                'right' => [
                    'image' => $this->image($this->duo_right_image, alt: $this->duo_right_image_alt),
                    'caption' => $this->duo_right_caption,
                ],
            ],
            'person' => [
                'name' => $this->person_name,
                'title' => $this->person_title,
                'photo' => $this->image($this->person_photo, alt: $this->person_photo_alt),
                'text' => $this->person_text,
            ],
            'newest_editions' => [
                'title' => $this->newest_title,
                'description' => $this->newest_description,
            ],
            'science' => [
                'title' => $this->science_title,
                'text' => $this->science_text,
                'button' => [
                    'text' => $this->science_button_text,
                    'url' => $this->science_button_url,
                ],
                'gallery' => $scienceGallery,
            ],
            'gallery' => $scienceGallery,
        ];
    }
}
