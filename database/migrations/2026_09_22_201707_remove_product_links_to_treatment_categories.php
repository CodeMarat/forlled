<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $treatmentCategoryIds = DB::table('product_categories')
            ->where('type', 'treatment')
            ->select('id');

        $productCategoryAssignments = DB::table('product_category_product as assignments')
            ->join('product_categories as categories', 'categories.id', '=', 'assignments.product_category_id')
            ->where('categories.type', 'product')
            ->select('assignments.product_id');

        $productsWithoutProductCategories = DB::table('products')
            ->where('type', 'product')
            ->whereIn('id', DB::table('product_category_product')
                ->whereIn('product_category_id', $treatmentCategoryIds)
                ->select('product_id'))
            ->whereNotIn('id', $productCategoryAssignments)
            ->pluck('id');

        if ($productsWithoutProductCategories->isNotEmpty()) {
            throw new RuntimeException('Products have only treatment categories: '.$productsWithoutProductCategories->implode(', '));
        }

        DB::table('product_category_product')
            ->whereIn('product_id', DB::table('products')->where('type', 'product')->select('id'))
            ->whereIn('product_category_id', $treatmentCategoryIds)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Removed legacy links cannot be restored without knowing their original assignments.
    }
};
