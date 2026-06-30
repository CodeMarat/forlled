<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class ProductCategoryResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...ProductCategoryListResource::make($this->resource)->resolve($request),
            'navigation_groups' => $this->when(
                filled($this->navigation_groups ?? null),
                fn (): array => $this->navigation_groups,
            ),
            'products' => $this->whenLoaded(
                'products',
                fn (): array => ProductCardResource::collection($this->products)->resolve($request),
            ),
        ];
    }
}
