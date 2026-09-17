<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('type')
                ->default('product')
                ->after('group_name')
                ->index();
        });

        if (Schema::hasColumn('products', 'catalogs')) {
            DB::table('product_categories')
                ->whereExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('products')
                        ->whereColumn('products.product_category_id', 'product_categories.id')
                        ->whereJsonContains('products.catalogs', 'treatment');
                })
                ->whereNotExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('products')
                        ->whereColumn('products.product_category_id', 'product_categories.id')
                        ->whereJsonContains('products.catalogs', 'product');
                })
                ->update(['type' => 'treatment']);

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('catalogs');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('products', 'catalogs')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('catalogs')
                    ->nullable()
                    ->after('product_category_id');
            });

            DB::table('products')
                ->join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
                ->select(['products.id', 'product_categories.type'])
                ->orderBy('products.id')
                ->chunk(200, function ($products): void {
                    foreach ($products as $product) {
                        DB::table('products')
                            ->where('id', $product->id)
                            ->update(['catalogs' => json_encode([$product->type], JSON_THROW_ON_ERROR)]);
                    }
                });
        }

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
