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
        Schema::table('contact_us', function (Blueprint $table) {
            $table->string('name_label')->nullable()->after('description');
            $table->string('email_label')->nullable()->after('name_label');
            $table->string('country_label')->nullable()->after('email_label');
            $table->string('city_label')->nullable()->after('country_label');
            $table->string('message_label')->nullable()->after('city_label');
            $table->string('submit_button_text')->nullable()->after('message_label');
            $table->text('success_message')->nullable()->after('submit_button_text');
            $table->json('country_options')->nullable()->after('success_message');
            $table->json('city_options')->nullable()->after('country_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_us', function (Blueprint $table) {
            $table->dropColumn([
                'name_label',
                'email_label',
                'country_label',
                'city_label',
                'message_label',
                'submit_button_text',
                'success_message',
                'country_options',
                'city_options',
            ]);
        });
    }
};
