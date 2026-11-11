<?php

namespace Database\Factories;

use App\Models\RepresentedCountry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RepresentedCountry>
 */
class RepresentedCountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->country(),
            'slug' => fake()->unique()->slug(),
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
