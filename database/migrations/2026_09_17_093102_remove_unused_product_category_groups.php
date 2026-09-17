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
        DB::table('product_categories')
            ->whereIn('group_name', ['series', 'skincare system'])
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('products')
                    ->whereColumn('products.product_category_id', 'product_categories.id');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Removed categories were unused placeholders and must not be recreated.
    }
};
