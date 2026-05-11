<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('technology_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_title')->nullable();
            $table->text('page_intro')->nullable();
            $table->string('delivery_system_title')->nullable();
            $table->text('delivery_system_description')->nullable();
            $table->text('delivery_system_secondary_text')->nullable();
            $table->string('delivery_system_image')->nullable();
            $table->string('method_title')->nullable();
            $table->text('method_description')->nullable();
            $table->string('method_image')->nullable();
            $table->json('method_benefits')->nullable();
            $table->string('ingredients_section_title')->nullable();
            $table->json('ingredient_cards')->nullable();
            $table->string('evidence_title')->nullable();
            $table->text('evidence_text')->nullable();
            $table->string('case_studies_title')->nullable();
            $table->text('case_studies_description')->nullable();
            $table->string('before_label')->nullable();
            $table->string('after_label')->nullable();
            $table->json('case_studies')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technology_pages');
    }
};
