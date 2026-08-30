<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class ProductResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...ProductCardResource::make($this->resource)->resolve($request),
            'description' => $this->description,
            'side_image' => $this->image($this->side_image, alt: $this->side_image_alt),
            'key_benefits' => $this->values($this->key_benefits, 'benefit'),
            'sections' => collect($this->detail_sections ?? [])
                ->filter(fn (mixed $section): bool => ! is_array($section) || (bool) ($section['is_visible'] ?? true))
                ->map(fn (array $section): array => Arr::except($section, ['is_visible']))
                ->values()
                ->all(),
            'recommendations_title' => $this->recommendations_title,
            'recommended_products' => $this->whenLoaded(
                'recommendedProducts',
                fn (): array => ProductCardResource::collection($this->recommendedProducts)->resolve($request),
            ),
            'combine_with_treatment' => [
                'title' => $this->combine_with_title,
                'left' => [
                    'title' => $this->combine_left_title,
                    'text' => $this->combine_left_text,
                ],
                'right' => [
                    'title' => $this->combine_right_title,
                    'text' => $this->combine_right_text,
                ],
            ],
            'navigation_groups' => $this->when(
                filled($this->navigation_groups ?? null),
                fn (): array => $this->navigation_groups,
            ),
        ];
    }
}
