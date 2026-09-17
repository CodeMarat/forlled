<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaginatedIndexRequest;
use App\Http\Resources\Api\V1\ProductCategoryGroupResource;
use App\Http\Resources\Api\V1\ProductListResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Products\ProductCategoryNavigationGrouper;
use App\Support\Products\ProductType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(
        protected ProductCategoryNavigationGrouper $navigationGrouper,
    ) {}

    public function index(PaginatedIndexRequest $request): AnonymousResourceCollection
    {
        $perPage = $request->perPage();
        $productsQuery = Product::query()
            ->where('is_active', true)
            ->whereHas('productCategory', fn ($query) => $query
                ->where('is_active', true)
                ->where('type', ProductType::Product->value))
            ->with('productCategory');

        $products = $productsQuery
            ->orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();

        return ProductListResource::collection($products);
    }

    public function show(string $product): ProductResource
    {
        $navigationCategories = ProductCategory::query()
            ->where('is_active', true)
            ->where('type', ProductType::Product->value)
            ->orderBy('sort_order')
            ->get();

        $productQuery = Product::query()
            ->where('slug', $product)
            ->where('is_active', true)
            ->whereHas('productCategory', fn ($query) => $query
                ->where('is_active', true)
                ->where('type', ProductType::Product->value))
            ->with([
                'productCategory',
                'productRecommendations.relatedProduct.productCategory',
            ]);

        $product = $productQuery->firstOrFail();

        $recommendedProducts = $product->productRecommendations
            ->pluck('relatedProduct')
            ->filter(fn ($relatedProduct) => $relatedProduct?->is_active
                && $relatedProduct->productCategory?->is_active
                && $relatedProduct->productCategory?->type === ProductType::Product)
            ->values();

        $product->setRelation('recommendedProducts', $recommendedProducts);
        $product->setAttribute(
            'navigation_groups',
            ProductCategoryGroupResource::collection(
                $this->navigationGrouper->group($navigationCategories),
            )->resolve(),
        );

        return ProductResource::make($product);
    }
}
