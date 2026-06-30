<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductCategoryGroupResource;
use App\Http\Resources\Api\V1\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Support\Products\ProductCategoryNavigationGrouper;
use Illuminate\Http\JsonResponse;

class ProductCategoryController extends Controller
{
    public function __construct(
        protected ProductCategoryNavigationGrouper $navigationGrouper,
    ) {}

    public function index(): JsonResponse
    {
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'groups' => ProductCategoryGroupResource::collection(
                $this->navigationGrouper->group($categories),
            )->resolve(),
        ]);
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

        $category->setAttribute(
            'navigation_groups',
            ProductCategoryGroupResource::collection(
                $this->navigationGrouper->group($navigationCategories),
            )->resolve(),
        );

        return ProductCategoryResource::make($category);
    }
}
