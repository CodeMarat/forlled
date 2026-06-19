<?php

namespace Database\Seeders;

use App\Models\FeaturedInPage;
use Illuminate\Database\Seeder;

class FeaturedInSeeder extends Seeder
{
    public function run(): void
    {
        FeaturedInPage::query()->firstOrCreate([
            'id' => 1,
        ], [
            'title' => 'FEATURED IN',
            'logos' => [],
        ]);
    }
}
