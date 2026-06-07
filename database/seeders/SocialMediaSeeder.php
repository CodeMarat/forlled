<?php

namespace Database\Seeders;

use App\Models\SocialMedia;
use Illuminate\Database\Seeder;

class SocialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SocialMedia::query()->firstOrCreate([
            'id' => 1,
        ], [
            'instagram_url' => 'https://instagram.com/forlled',
            'facebook_url' => 'https://facebook.com/forlled',
            'youtube_url' => 'https://youtube.com/@forlled',
            'linkedin_url' => 'https://linkedin.com/company/forlled',
        ]);
    }
}
