<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class ProductCardResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'size' => $this->size,
            'listing_description' => $this->listing_description,
            'hero_image' => $this->image($this->hero_image, 'card', $this->hero_image_alt),
            'is_favorite' => (bool) $this->is_favorite,
            'category' => $this->whenLoaded(
                'productCategory',
                fn (): ProductCategoryListResource => ProductCategoryListResource::make($this->productCategory),
            ),
        ];
    }
}
