<?php

namespace Database\Factories;

use App\Models\TreatmentPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreatmentPage>
 */
class TreatmentPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => 'treatments',
            'meta_title' => 'Treatments — FORLLE\'D',
            'meta_description' => fake()->sentence(),
            'hero_title' => 'TREATMENTS',
            'hero_description' => fake()->paragraph(),
            'hero_button_text' => 'BECOME A PARTNER',
            'hero_button_url' => '/become-partner',
            'hero_image' => null,
        ];
    }
}
