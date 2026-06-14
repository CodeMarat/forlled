<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class BlogPostListResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'featured_image' => $this->image($this->featured_image, 'card', $this->featured_image_alt),
            'published_at' => $this->published_at?->toIso8601String(),
            'sort_order' => $this->sort_order,
        ];
    }
}
