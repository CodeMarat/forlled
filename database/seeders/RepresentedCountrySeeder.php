<?php

namespace Database\Seeders;

use App\Models\RepresentedCountry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RepresentedCountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = json_decode(file_get_contents(__DIR__.'/represented_countries.json'), true, flags: JSON_THROW_ON_ERROR);

        foreach ($countries as $index => $name) {
            RepresentedCountry::query()->firstOrCreate(
                ['source_key' => sha1(Str::lower(trim($name)))],
                ['name' => $name, 'slug' => Str::slug($name), 'sort_order' => $index, 'is_active' => true],
            );
        }
    }
}
