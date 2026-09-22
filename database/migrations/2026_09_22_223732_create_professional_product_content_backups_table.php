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
        Schema::create('professional_product_content_backups', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->primary();
            $table->longText('original_content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('professional_product_content_backups') && DB::table('professional_product_content_backups')->exists()) {
            throw new RuntimeException('Professional product content backup must be exported before rolling back this migration.');
        }

        Schema::dropIfExists('professional_product_content_backups');
    }
};
