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
        Schema::create('professional_product_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->foreignId('treatment_product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_product_copies');
    }
};
