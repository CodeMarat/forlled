<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductCategoryGroupResource;
use App\Http\Resources\Api\V1\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Support\Products\ProductCategoryNavigationGrouper;
use App\Support\Products\ProductCatalogQuery;
use App\Support\Products\ProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;

class TreatmentProductCategoryController extends Controller
{
    public function __construct(
        protected ProductCategoryNavigationGrouper $navigationGrouper,
    ) {}

    public function index(): JsonResponse
    {
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $this->filterTreatmentProducts($query))
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
            ->whereHas('products', fn ($query) => $this->filterTreatmentProducts($query))
            ->orderBy('sort_order')
            ->get();

        $category = ProductCategory::query()
            ->where('slug', $productCategory)
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $this->filterTreatmentProducts($query))
            ->with([
                'products' => fn ($query) => $this->filterTreatmentProducts($query)
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

    /**
     * @param  Builder|HasMany  $query
     * @return Builder|HasMany
     */
    protected function filterTreatmentProducts(Builder|HasMany $query): Builder|HasMany
    {
        $query->where('is_active', true);

        return ProductCatalogQuery::forCatalog($query, ProductType::Treatment->value);
    }
}
