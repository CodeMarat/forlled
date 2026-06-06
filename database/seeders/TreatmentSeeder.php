<?php

namespace Database\Seeders;

use App\Models\Treatment;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $treatments = [
            [
                'name' => 'SENSKI SKIN CREAM',
                'slug' => 'senski-skin-cream',
                'description' => 'The procedure with powerful antioxidant protection for fading, dull, stressed skin of urban dwellers.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'PLATINUM BOOST',
                'slug' => 'platinum-boost',
                'description' => 'A balancing treatment designed to support skin vitality while improving comfort and visible radiance.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($treatments as $treatment) {
            Treatment::query()->updateOrCreate(
                ['slug' => $treatment['slug']],
                $treatment,
            );
        }
    }
}
