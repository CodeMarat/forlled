<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaginatedIndexRequest;
use App\Http\Resources\Api\V1\BlogPostListResource;
use App\Http\Resources\Api\V1\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogPostController extends Controller
{
    public function index(PaginatedIndexRequest $request): AnonymousResourceCollection
    {
        $perPage = $request->perPage();

        $blogPosts = BlogPost::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()))
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();

        return BlogPostListResource::collection($blogPosts);
    }

    public function show(string $blogPost): BlogPostResource
    {
        $post = BlogPost::query()
            ->where('slug', $blogPost)
            ->where('status', 'published')
            ->where(fn ($query) => $query
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()))
            ->firstOrFail();

        return BlogPostResource::make($post);
    }
}
