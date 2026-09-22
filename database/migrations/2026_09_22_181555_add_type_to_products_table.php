<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $productsWithMixedCategoryTypes = DB::table('product_category_product as assignments')
            ->join('product_categories as categories', 'categories.id', '=', 'assignments.product_category_id')
            ->select('assignments.product_id')
            ->groupBy('assignments.product_id')
            ->havingRaw('COUNT(DISTINCT categories.type) > 1');

        $hasMixedCategoryTypes = DB::query()
            ->fromSub($productsWithMixedCategoryTypes, 'mixed_products')
            ->exists();

        if ($hasMixedCategoryTypes) {
            throw new \RuntimeException('Products assigned to both product and treatment categories must be resolved before adding products.type.');
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('type')
                ->default('product')
                ->after('slug')
                ->index();
        });

        DB::table('products')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('product_category_product as assignments')
                    ->join('product_categories as categories', 'categories.id', '=', 'assignments.product_category_id')
                    ->whereColumn('assignments.product_id', 'products.id')
                    ->where('categories.type', 'treatment');
            })
            ->update(['type' => 'treatment']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
