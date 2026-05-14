<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class ProductListResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ProductCardResource::make($this->resource)->resolve($request);
    }
}
