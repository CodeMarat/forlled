<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class FeaturedInPageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'logos' => array_values(array_filter(array_map(
                fn (mixed $logo): ?array => is_array($logo) && filled($logo['image'] ?? null)
                    ? $this->image($logo['image'], 'card', $logo['alt'] ?? null)
                    : null,
                is_array($this->logos) ? $this->logos : [],
            ))),
        ];
    }
}
