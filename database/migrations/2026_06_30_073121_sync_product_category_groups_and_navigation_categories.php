<?php

use App\Models\ProductCategory;
use App\Support\Products\ProductCategoryNavigationDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('product_categories')) {
            return;
        }

        $slugs = array_column(ProductCategoryNavigationDefaults::categories(), 'slug');

        ProductCategory::query()
            ->whereIn('slug', $slugs)
            ->whereDoesntHave('products')
            ->delete();
    }
};
