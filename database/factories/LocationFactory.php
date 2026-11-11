<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(3),
            'sort_order' => fake()->numberBetween(0, 20),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'company' => fake()->company(),
            'address' => fake()->streetAddress(),
            'phones' => [fake()->phoneNumber()],
            'email' => fake()->safeEmail(),
            'website' => fake()->url(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'is_active' => true,
        ];
    }
}
