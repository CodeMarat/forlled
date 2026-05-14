<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class BlogPostResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...BlogPostListResource::make($this->resource)->resolve($request),
            'content' => $this->content,
            'status' => $this->status,
        ];
    }
}
