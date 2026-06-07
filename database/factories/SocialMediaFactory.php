<?php

namespace Database\Factories;

use App\Models\SocialMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialMedia>
 */
class SocialMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instagram_url' => 'https://instagram.com/forlled',
            'facebook_url' => 'https://facebook.com/forlled',
            'youtube_url' => 'https://youtube.com/@forlled',
            'linkedin_url' => 'https://linkedin.com/company/forlled',
        ];
    }
}
