<?php

namespace App\Http\Resources\Api\V1;

use App\Support\Products\ProductType;
use Illuminate\Http\Request;

class ProductCategoryListResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'group_name' => $this->group_name,
            'type' => $this->type instanceof ProductType ? $this->type->value : $this->type,
            'slug' => $this->slug,
            'type_label' => $this->type_label,
            'hero_title' => $this->hero_title,
            'hero_image' => $this->image($this->hero_image, 'card'),
            'sort_order' => $this->sort_order,
        ];
    }
}
