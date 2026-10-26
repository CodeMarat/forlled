<?php

namespace Database\Factories;

use App\Models\PrivacyPolicyPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrivacyPolicyPage>
 */
class PrivacyPolicyPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Privacy Policy',
            'content' => '<h2>Website visitors</h2><p>Policy text.</p>',
        ];
    }
}
