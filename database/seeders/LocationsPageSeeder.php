<?php

namespace Database\Seeders;

use App\Models\LocationsPage;
use Illuminate\Database\Seeder;

class LocationsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $page = LocationsPage::query()->firstOrCreate(
            ['slug' => 'locations'],
            [
                'hero_title' => 'Where to find us',
                'hero_description' => 'Step into a refined beauty experience at our location. Discover expert care, premium products, and personalized service.',
            ],
        );

        $defaults = [
            'us_hero_eyebrow' => 'Location finder',
            'us_hero_title' => "Find a Forlle'd clinic near you",
            'us_hero_description' => "Experience a professional Forlle'd treatment at authorised medical spas, dermatology clinics and esthetician studios across the United States — or pick up home care products from a partner near you.",
            'us_locations_title' => 'Our locations',
            'us_locations_description' => "Discover authorised Forlle'd partners across the United States.",
        ];

        foreach ($defaults as $field => $value) {
            if (blank($page->{$field})) {
                $page->{$field} = $value;
            }
        }

        if ($page->isDirty()) {
            $page->save();
        }
    }
}
