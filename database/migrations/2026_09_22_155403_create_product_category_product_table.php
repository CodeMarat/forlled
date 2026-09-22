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
        Schema::create('product_category_product', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('product_category_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['product_id', 'product_category_id']);
        });

        DB::table('product_category_product')->insertUsing(
            ['product_id', 'product_category_id'],
            DB::table('products')
                ->whereNotNull('product_category_id')
                ->select(['id', 'product_category_id']),
        );

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('product_category_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        DB::table('product_category_product')
            ->selectRaw('product_id, MIN(product_category_id) as product_category_id')
            ->groupBy('product_id')
            ->orderBy('product_id')
            ->chunk(200, function ($assignments): void {
                foreach ($assignments as $assignment) {
                    DB::table('products')
                        ->where('id', $assignment->product_id)
                        ->update(['product_category_id' => $assignment->product_category_id]);
                }
            });

        Schema::dropIfExists('product_category_product');
    }
};
