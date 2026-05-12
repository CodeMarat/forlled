<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
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
            'product_category_id' => ProductCategory::factory(),
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
                ],
            ],
            'recommendations_title' => 'HOME ROUTINE RECOMMENDATIONS',
            'combine_with_title' => 'COMBINE WITH A TREATMENT',
            'combine_left_title' => 'Recommended with professional treatments',
            'combine_left_text' => fake()->paragraph(),
            'combine_right_title' => 'Elevate your results',
            'combine_right_text' => fake()->paragraph(),
            'is_favorite' => false,
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
