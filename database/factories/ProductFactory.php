<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'listing_description' => fake()->sentence(),
            'size' => '150 ml / 5 fl oz',
            'hero_image' => null,
            'side_image' => null,
            'key_benefits' => [
                ['benefit' => fake()->sentence(4)],
                ['benefit' => fake()->sentence(4)],
            ],
            'detail_sections' => [
                [
                    'title' => 'Indications',
                    'content' => fake()->paragraph(),
                    'is_visible' => true,
                ],
            ],
            'recommendations_title' => null,
            'combine_with_title' => null,
            'combine_left_title' => null,
            'combine_left_text' => null,
            'combine_right_title' => null,
            'combine_right_text' => null,
            'is_favorite' => false,
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
