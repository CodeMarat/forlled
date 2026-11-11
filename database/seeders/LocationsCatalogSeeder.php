<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LocationsCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            LocationsPageSeeder::class,
            RepresentedCountrySeeder::class,
            UsSalonSeeder::class,
        ]);
    }
}
