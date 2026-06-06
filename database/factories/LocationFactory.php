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
            'country' => fake()->country(),
            'country_key' => fake()->slug(1),
            'city' => fake()->city(),
            'company' => fake()->company(),
            'address' => fake()->streetAddress(),
            'phones' => [fake()->phoneNumber()],
            'email' => fake()->safeEmail(),
            'map_pin_x' => fake()->numberBetween(0, 100),
            'map_pin_y' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
