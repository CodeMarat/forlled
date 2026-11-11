<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_TABLE = 'legacy_locations_2026_11_09';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable(self::LEGACY_TABLE)) {
            if (Schema::hasTable('locations')) {
                if (Schema::hasColumn('locations', 'source_key') && ! Schema::hasColumn('locations', 'country_key')) {
                    return;
                }

                throw new RuntimeException('Legacy locations archive already exists, but the active locations table is not a USA salons table.');
            }
        } else {
            Schema::rename('locations', self::LEGACY_TABLE);
        }

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('city');
            $table->string('state', 2)->nullable();
            $table->string('company');
            $table->string('address');
            $table->json('phones')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('source_key')->nullable()->unique();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('google_address_place_id')->nullable();
            $table->timestamp('place_lookup_attempted_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('locations')->exists()) {
            throw new RuntimeException('Cannot roll back USA salons while active location records exist.');
        }

        Schema::drop('locations');
        Schema::rename(self::LEGACY_TABLE, 'locations');
    }
};
