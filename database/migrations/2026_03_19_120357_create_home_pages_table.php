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
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->text('intro_text')->nullable();
            $table->string('favorites_title')->nullable();
            $table->string('duo_left_image')->nullable();
            $table->string('duo_left_caption')->nullable();
            $table->string('duo_right_image')->nullable();
            $table->string('duo_right_caption')->nullable();
            $table->string('person_name')->nullable();
            $table->string('person_title')->nullable();
            $table->string('person_photo')->nullable();
            $table->text('person_text')->nullable();
            $table->string('newest_title')->nullable();
            $table->text('newest_description')->nullable();
            $table->string('science_title')->nullable();
            $table->text('science_text')->nullable();
            $table->string('science_button_text')->nullable();
            $table->string('science_button_url')->nullable();
            $table->string('gallery_image_1')->nullable();
            $table->string('gallery_image_2')->nullable();
            $table->string('gallery_image_3')->nullable();
            $table->string('gallery_image_4')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_pages');
    }
};
