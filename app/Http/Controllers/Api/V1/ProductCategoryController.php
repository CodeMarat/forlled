<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaginatedIndexRequest;
use App\Http\Resources\Api\V1\ProductCategoryListResource;
use App\Http\Resources\Api\V1\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductCategoryController extends Controller
{
    public function index(PaginatedIndexRequest $request): AnonymousResourceCollection
    {
        $perPage = $request->perPage();

        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();

        return ProductCategoryListResource::collection($categories);
    }

    public function show(string $productCategory): ProductCategoryResource
    {
        $navigationCategories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $category = ProductCategory::query()
            ->where('slug', $productCategory)
            ->where('is_active', true)
            ->with([
                'products' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with('productCategory')
                    ->orderBy('sort_order'),
            ])
            ->firstOrFail();

        $category->setRelation('navigationCategories', $navigationCategories);

        return ProductCategoryResource::make($category);
    }
}
