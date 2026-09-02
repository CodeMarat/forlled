<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Products\ProductCategoryNavigationDefaults;
use App\Support\Products\ProductDetailSections;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProductPagesTextSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('product_categories') || ! Schema::hasTable('products')) {
            return;
        }

        $jsonPath = base_path('product-pages-content.json');

        if (! is_file($jsonPath)) {
            return;
        }

        $payload = json_decode((string) file_get_contents($jsonPath), true);

        if (! is_array($payload)) {
            return;
        }

        $categories = $this->seedCategories();
        $products = $payload['products'] ?? [];

        if (! is_array($products)) {
            return;
        }

        foreach ($products as $product) {
            if (! is_array($product)) {
                continue;
            }

            $categorySlug = $product['category_slug'] ?? null;
            $attributes = $product['attributes'] ?? null;
            $slug = $product['slug'] ?? null;

            if (! is_string($categorySlug) || ! is_array($attributes) || ! is_string($slug)) {
                continue;
            }

            $category = $categories[$categorySlug] ?? null;

            if (! $category instanceof ProductCategory) {
                continue;
            }

            Product::query()->updateOrCreate(
                ['slug' => $slug],
                array_merge($attributes, [
                    'detail_sections' => ProductDetailSections::makeVisible((array) ($attributes['detail_sections'] ?? [])),
                    'product_category_id' => $category->id,
                ]),
            );
        }
    }

    /**
     * @return array<string, ProductCategory>
     */
    protected function seedCategories(): array
    {
        $categories = [];

        foreach (ProductCategoryNavigationDefaults::categories() as $attributes) {
            $category = ProductCategory::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );

            $categories[$attributes['slug']] = $category;
        }

        return $categories;
    }
}
