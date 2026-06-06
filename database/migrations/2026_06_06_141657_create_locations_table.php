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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('country');
            $table->string('country_key');
            $table->string('city');
            $table->string('company');
            $table->string('address');
            $table->json('phones')->nullable();
            $table->string('email')->nullable();
            $table->unsignedTinyInteger('map_pin_x')->nullable();
            $table->unsignedTinyInteger('map_pin_y')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
