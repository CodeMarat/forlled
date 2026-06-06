<?php

namespace Database\Seeders;

use App\Models\TreatmentPage;
use Illuminate\Database\Seeder;

class TreatmentPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TreatmentPage::query()->updateOrCreate([
            'slug' => 'treatments',
        ], [
            'meta_title' => 'Treatments — FORLLE\'D',
            'meta_description' => 'Healthy skin starts with the right care.',
            'hero_title' => 'TREATMENTS',
            'hero_description' => 'Healthy skin starts with the right care. Our treatments nourish, repair, and protect your skin while helping you relax, leaving it refreshed, balanced, and radiant.',
            'hero_button_text' => 'BECOME A PARTNER',
            'hero_button_url' => '/become-partner',
            'hero_image' => null,
        ]);
    }
}
