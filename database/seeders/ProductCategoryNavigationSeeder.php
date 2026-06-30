<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Support\Products\ProductCategoryNavigationDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProductCategoryNavigationSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('product_categories')) {
            return;
        }

        foreach (ProductCategoryNavigationDefaults::categories() as $category) {
            ProductCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
