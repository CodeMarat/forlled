<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('locations_pages', function (Blueprint $table) {
            $table->string('us_hero_eyebrow')->nullable();
            $table->string('us_hero_title')->nullable();
            $table->text('us_hero_description')->nullable();
            $table->string('us_hero_image')->nullable();
            $table->string('us_locations_title')->nullable();
            $table->text('us_locations_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations_pages', function (Blueprint $table) {
            $table->dropColumn([
                'us_hero_eyebrow',
                'us_hero_title',
                'us_hero_description',
                'us_hero_image',
                'us_locations_title',
                'us_locations_description',
            ]);
        });
    }
};
