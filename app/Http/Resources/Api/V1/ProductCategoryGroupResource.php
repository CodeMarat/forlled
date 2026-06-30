<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class ProductCategoryGroupResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this['name'],
            'categories' => ProductCategoryListResource::collection($this['categories'])->resolve($request),
        ];
    }
}
