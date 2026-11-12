<?php

namespace Database\Factories;

use App\Models\MailNotificationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailNotificationSetting>
 */
class MailNotificationSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'partner_request_email' => fake()->safeEmail(),
            'contact_us_request_email' => fake()->safeEmail(),
        ];
    }
}
