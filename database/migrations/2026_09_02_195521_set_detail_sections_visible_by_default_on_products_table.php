<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\Products\ProductDetailSections;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'detail_sections')) {
            return;
        }

        DB::table('products')
            ->select(['id', 'detail_sections'])
            ->orderBy('id')
            ->chunkById(100, function ($products): void {
                foreach ($products as $product) {
                    $sections = json_decode((string) $product->detail_sections, true);

                    if (! is_array($sections)) {
                        continue;
                    }

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'detail_sections' => json_encode(
                                ProductDetailSections::makeVisible($sections),
                                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                            ),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
