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
        Schema::create('become_partner_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('submit_button_text')->nullable();

            $table->string('first_name_label')->nullable();
            $table->string('last_name_label')->nullable();
            $table->string('country_label')->nullable();
            $table->string('city_label')->nullable();
            $table->string('company_label')->nullable();
            $table->string('company_type_label')->nullable();
            $table->string('position_label')->nullable();
            $table->string('email_label')->nullable();
            $table->string('phone_label')->nullable();
            $table->string('website_label')->nullable();
            $table->string('message_label')->nullable();

            $table->json('country_options')->nullable();
            $table->json('city_options')->nullable();
            $table->json('company_type_options')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('become_partner_pages');
    }
};
