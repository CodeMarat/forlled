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
use App\Support\Products\ProductCatalogQuery;
use App\Support\Products\ProductType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class TreatmentProductController extends Controller
{
    public function __construct(
        protected ProductCategoryNavigationGrouper $navigationGrouper,
    ) {}

    public function index(PaginatedIndexRequest $request): AnonymousResourceCollection
    {
        $perPage = $request->perPage();
        $hasCatalogsColumn = DatabaseSchema::hasColumn('products', 'catalogs');

        $productsQuery = Product::query()
            ->where('is_active', true)
            ->whereHas('productCategory', fn ($query) => $query->where('is_active', true))
            ->with('productCategory');

        if ($hasCatalogsColumn || DatabaseSchema::hasColumn('products', 'type')) {
            ProductCatalogQuery::forCatalog($productsQuery, ProductType::Treatment->value);
        }

        $products = $productsQuery
            ->orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();

        return ProductListResource::collection($products);
    }

    public function show(string $slug): ProductResource
    {
        $hasCatalogsColumn = DatabaseSchema::hasColumn('products', 'catalogs');

        $navigationCategories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $productQuery = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('productCategory', fn ($query) => $query->where('is_active', true))
            ->with([
                'productCategory',
                'productRecommendations.relatedProduct.productCategory',
            ]);

        if ($hasCatalogsColumn || DatabaseSchema::hasColumn('products', 'type')) {
            ProductCatalogQuery::forCatalog($productQuery, ProductType::Treatment->value);
        }

        $product = $productQuery->firstOrFail();

        $recommendedProducts = $product->productRecommendations
            ->pluck('relatedProduct')
            ->filter(fn ($relatedProduct) => $relatedProduct?->is_active && (! $hasCatalogsColumn || $relatedProduct?->isCatalogEnabled(ProductType::Treatment->value)))
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
