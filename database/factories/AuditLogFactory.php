<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\HomePage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'auditable_type' => HomePage::class,
            'auditable_id' => 1,
            'page' => 'Home page',
            'record_title' => 'Home page settings',
            'event' => fake()->randomElement(['created', 'updated', 'deleted']),
            'changed_fields' => ['hero_title', 'science_title'],
            'old_values' => ['hero_title' => 'Before'],
            'new_values' => ['hero_title' => 'After'],
            'request_url' => '/admin/home-page',
        ];
    }
}
