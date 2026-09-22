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
        Schema::create('home_product_menu_backups', function (Blueprint $table) {
            $table->id();
            $table->longText('categories_snapshot');
            $table->longText('links_snapshot');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('home_product_menu_backups') && DB::table('home_product_menu_backups')->exists()) {
            throw new RuntimeException('Home product menu backup must be exported before rolling back this migration.');
        }

        Schema::dropIfExists('home_product_menu_backups');
    }
};
