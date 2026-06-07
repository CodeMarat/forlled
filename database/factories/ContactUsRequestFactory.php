<?php

namespace Database\Factories;

use App\Models\ContactUsRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactUsRequest>
 */
class ContactUsRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'message' => fake()->paragraph(),
            'status' => 'new',
            'admin_note' => null,
        ];
    }
}
