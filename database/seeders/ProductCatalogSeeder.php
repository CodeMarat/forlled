<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductRecommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('product_categories') || ! Schema::hasTable('products') || ! Schema::hasTable('product_related')) {
            return;
        }

        if (! Schema::hasColumn('products', 'product_category_id') || ! Schema::hasColumn('products', 'recommendations_title')) {
            return;
        }

        $categories = collect([
            [
                'name' => 'Cleansers',
                'slug' => 'cleansers',
                'type_label' => 'TYPE',
                'hero_title' => 'CLEANSERS',
                'hero_image' => null,
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Lotions',
                'slug' => 'lotions',
                'type_label' => 'TYPE',
                'hero_title' => 'LOTIONS',
                'hero_image' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Serums',
                'slug' => 'serums',
                'type_label' => 'TYPE',
                'hero_title' => 'SERUMS',
                'hero_image' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Masks',
                'slug' => 'masks',
                'type_label' => 'TYPE',
                'hero_title' => 'MASKS',
                'hero_image' => null,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Creams & Emulsions',
                'slug' => 'creams-emulsions',
                'type_label' => 'TYPE',
                'hero_title' => 'CREAMS & EMULSIONS',
                'hero_image' => null,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Eye Care',
                'slug' => 'eye-care',
                'type_label' => 'TYPE',
                'hero_title' => 'EYE CARE',
                'hero_image' => null,
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Special Care',
                'slug' => 'special-care',
                'type_label' => 'TYPE',
                'hero_title' => 'SPECIAL CARE',
                'hero_image' => null,
                'sort_order' => 6,
                'is_active' => true,
            ],
        ])->mapWithKeys(fn (array $attributes) => [
            $attributes['slug'] => ProductCategory::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            ),
        ]);

        $products = collect([
            [
                'category' => 'cleansers',
                'name' => 'Hyalogy Remover For Point Make-Up',
                'slug' => 'hyalogy-remover-for-point-make-up',
                'description' => 'This wonderful cleanser is developed, particularly for fast removing make up, including water-resistant, from the skin around the eyes and lips. It helps remove toxins, soothes irritations, activates microcirculation and significantly increases the efficiency of products which are subsequently applied. It also has anti-inflammatory and anti-allergenic properties.',
                'listing_description' => 'This wonderful cleanser is developed, particularly for fast removing make up, including water-resistant, from the skin around the eyes and lips.',
                'size' => '15 ml / 0.5 fl oz',
                'hero_image' => null,
                'side_image' => null,
                'key_benefits' => [
                    ['benefit' => 'Gently cleanses skin surface'],
                    ['benefit' => 'Removes waterproof makeup'],
                    ['benefit' => "Prevents fine lines and 'crow's feet'"],
                    ['benefit' => 'Prevents whiteheads, dark circles and swelling'],
                    ['benefit' => 'Provides anti-inflammatory effect'],
                ],
                'detail_sections' => [
                    ['title' => 'Indications', 'content' => '<p>Designed for gentle yet effective cleansing around the eyes and lips, especially when removing long-wear or waterproof makeup.</p>'],
                    ['title' => 'Product density', 'content' => '<p>Lightweight two-phase liquid texture that spreads evenly and lifts impurities without friction.</p>'],
                    ['title' => 'Active ingredients', 'content' => '<p>Includes soothing components that help reduce irritation while supporting skin comfort and clarity.</p>'],
                    ['title' => 'Before/after', 'content' => '<p>Leaves the delicate eye area visibly cleaner, softer and more refreshed after use.</p>'],
                    ['title' => 'How to use', 'content' => '<p>Apply to a cotton pad, place onto the eye or lip area for a few seconds, then gently wipe away makeup.</p>'],
                ],
                'recommendations_title' => 'HOME ROUTINE RECOMMENDATIONS',
                'combine_with_title' => 'COMBINE WITH A TREATMENT',
                'combine_left_title' => 'Recommended with professional treatments',
                'combine_left_text' => '<p>For optimal effectiveness, combine with a deep cleansing facial or barrier-repair hydration treatment. This approach helps enhance ingredient penetration, improve skin clarity, and support long-term skin health.</p>',
                'combine_right_title' => 'Elevate your results',
                'combine_right_text' => '<p>Pair with a recovery facial or intensive hydration treatment to maximize purification. This synergy helps refine texture, improve luminosity, and support healthier-looking skin over time.</p>',
                'is_favorite' => true,
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'category' => 'cleansers',
                'name' => 'Hyalogy P-Effect Clearance Cleansing',
                'slug' => 'hyalogy-p-effect-clearance-cleansing',
                'description' => 'The first stage cleanser designed particularly for the skin exposed to stress and pollutants of contemporary city life. It thoroughly removes surface impurities and helps maintain balance without stripping the skin.',
                'listing_description' => 'The first stage cleanser designed particularly for the skin exposed to stress and pollutants of contemporary city life.',
                'size' => '100 ml / 3.4 fl oz',
                'hero_image' => null,
                'side_image' => null,
                'key_benefits' => [
                    ['benefit' => 'Removes daily impurities'],
                    ['benefit' => 'Supports skin comfort'],
                    ['benefit' => 'Prepares the skin for the next care step'],
                ],
                'detail_sections' => [
                    ['title' => 'Indications', 'content' => '<p>Suitable for stressed, dull, urban skin exposed to pollution and environmental aggressors.</p>'],
                    ['title' => 'How to use', 'content' => '<p>Apply to dry skin, massage gently, then rinse or remove with damp cotton pads.</p>'],
                ],
                'recommendations_title' => 'HOME ROUTINE RECOMMENDATIONS',
                'combine_with_title' => 'COMBINE WITH A TREATMENT',
                'combine_left_title' => 'Recommended with professional treatments',
                'combine_left_text' => '<p>Works well as a preparation step before professional cleansing and detox protocols.</p>',
                'combine_right_title' => 'Elevate your results',
                'combine_right_text' => '<p>Use consistently to improve skin comfort and maintain a cleaner, brighter complexion.</p>',
                'is_favorite' => false,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'category' => 'cleansers',
                'name' => 'Hyalogy P-Effect Re-Purance Wash',
                'slug' => 'hyalogy-p-effect-re-purance-wash',
                'description' => 'This soft foam is designed as a second stage cleanser to dissolve and cleanse skin impurities by stimulating microcirculation and supporting a refined, fresh appearance.',
                'listing_description' => 'This soft foam is designed as a second stage cleanser to dissolve and cleanse skin impurities by stimulating microcirculation.',
                'size' => '100 ml / 3.4 fl oz',
                'hero_image' => null,
                'side_image' => null,
                'key_benefits' => [
                    ['benefit' => 'Provides a refined cleanse'],
                    ['benefit' => 'Supports microcirculation'],
                    ['benefit' => 'Leaves skin feeling soft and fresh'],
                ],
                'detail_sections' => [
                    ['title' => 'Indications', 'content' => '<p>Use as a second cleansing stage when the skin needs a refreshed, thoroughly purified finish.</p>'],
                    ['title' => 'How to use', 'content' => '<p>Lather with water, apply to the face using circular movements, then rinse thoroughly.</p>'],
                ],
                'recommendations_title' => 'HOME ROUTINE RECOMMENDATIONS',
                'combine_with_title' => 'COMBINE WITH A TREATMENT',
                'combine_left_title' => 'Recommended with professional treatments',
                'combine_left_text' => '<p>Combines especially well with purification and balancing treatment protocols.</p>',
                'combine_right_title' => 'Elevate your results',
                'combine_right_text' => '<p>Helps maintain a clean, comfortable skin feel between professional appointments.</p>',
                'is_favorite' => false,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'category' => 'cleansers',
                'name' => 'Hyalogy Creamy Wash',
                'slug' => 'hyalogy-creamy-wash',
                'description' => 'The light cleansing foam that contains derivatives of coconut oil is ideal for the skin that is hypersensitive and suffering from irritation. It gently cleanses while helping maintain softness and comfort.',
                'listing_description' => 'The light cleansing foam that contains derivatives of coconut oil is ideal for the skin that is hypersensitive and suffering from irritation.',
                'size' => '100 ml / 3.4 fl oz',
                'hero_image' => null,
                'side_image' => null,
                'key_benefits' => [
                    ['benefit' => 'Gentle cleansing for sensitive skin'],
                    ['benefit' => 'Helps maintain softness and comfort'],
                    ['benefit' => 'Suitable for daily use'],
                ],
                'detail_sections' => [
                    ['title' => 'Indications', 'content' => '<p>Recommended for sensitive, reactive or irritation-prone skin that requires a soft cleansing routine.</p>'],
                    ['title' => 'How to use', 'content' => '<p>Use morning and evening on damp skin, massage gently and rinse well.</p>'],
                ],
                'recommendations_title' => 'HOME ROUTINE RECOMMENDATIONS',
                'combine_with_title' => 'COMBINE WITH A TREATMENT',
                'combine_left_title' => 'Recommended with professional treatments',
                'combine_left_text' => '<p>Supports calming and barrier-restoring treatments by preparing the skin gently.</p>',
                'combine_right_title' => 'Elevate your results',
                'combine_right_text' => '<p>Regular use helps keep sensitive skin comfortable and visibly cleaner.</p>',
                'is_favorite' => false,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'category' => 'lotions',
                'name' => 'Hyalogy Lotion For Daily Balance',
                'slug' => 'hyalogy-lotion-for-daily-balance',
                'description' => 'A balancing lotion that helps restore comfort after cleansing and prepares the skin for the next step of the routine.',
                'listing_description' => 'A balancing lotion that helps restore comfort after cleansing.',
                'size' => '120 ml / 4.0 fl oz',
                'hero_image' => null,
                'side_image' => null,
                'key_benefits' => [
                    ['benefit' => 'Balances the skin after cleansing'],
                    ['benefit' => 'Improves comfort'],
                ],
                'detail_sections' => [
                    ['title' => 'How to use', 'content' => '<p>Apply with hands or a cotton pad after cleansing and before serum or cream.</p>'],
                ],
                'recommendations_title' => 'HOME ROUTINE RECOMMENDATIONS',
                'combine_with_title' => 'COMBINE WITH A TREATMENT',
                'combine_left_title' => 'Recommended with professional treatments',
                'combine_left_text' => '<p>Complements hydration-focused protocols.</p>',
                'combine_right_title' => 'Elevate your results',
                'combine_right_text' => '<p>Creates a comfortable base layer for the rest of the routine.</p>',
                'is_favorite' => false,
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'category' => 'serums',
                'name' => 'Hyalogy Serum For Radiance',
                'slug' => 'hyalogy-serum-for-radiance',
                'description' => 'A lightweight serum designed to support luminosity, softness and a visibly smoother surface.',
                'listing_description' => 'A lightweight serum designed to support luminosity and softness.',
                'size' => '30 ml / 1.0 fl oz',
                'hero_image' => null,
                'side_image' => null,
                'key_benefits' => [
                    ['benefit' => 'Supports radiance'],
                    ['benefit' => 'Helps smooth texture'],
                ],
                'detail_sections' => [
                    ['title' => 'How to use', 'content' => '<p>Apply after lotion and before cream, morning and evening.</p>'],
                ],
                'recommendations_title' => 'HOME ROUTINE RECOMMENDATIONS',
                'combine_with_title' => 'COMBINE WITH A TREATMENT',
                'combine_left_title' => 'Recommended with professional treatments',
                'combine_left_text' => '<p>Pairs well with revitalising treatment programs.</p>',
                'combine_right_title' => 'Elevate your results',
                'combine_right_text' => '<p>Supports a brighter, more refined complexion over time.</p>',
                'is_favorite' => false,
                'sort_order' => 0,
                'is_active' => true,
            ],
        ])->mapWithKeys(function (array $product) use ($categories) {
            $category = $categories->get($product['category']);

            unset($product['category']);

            $product['product_category_id'] = $category?->id;

            return [
                $product['slug'] => Product::query()->updateOrCreate(
                    ['slug' => $product['slug']],
                    $product,
                ),
            ];
        });

        $this->syncRecommendations(
            $products->get('hyalogy-remover-for-point-make-up'),
            [
                'hyalogy-creamy-wash',
                'hyalogy-lotion-for-daily-balance',
                'hyalogy-serum-for-radiance',
            ],
            $products,
        );

        $this->syncRecommendations(
            $products->get('hyalogy-p-effect-clearance-cleansing'),
            [
                'hyalogy-p-effect-re-purance-wash',
                'hyalogy-lotion-for-daily-balance',
                'hyalogy-serum-for-radiance',
            ],
            $products,
        );

        $this->syncRecommendations(
            $products->get('hyalogy-creamy-wash'),
            [
                'hyalogy-remover-for-point-make-up',
                'hyalogy-lotion-for-daily-balance',
                'hyalogy-serum-for-radiance',
            ],
            $products,
        );
    }

    /**
     * @param  array<string, Product>|Collection<string, Product>  $products
     * @param  array<int, string>  $relatedSlugs
     */
    protected function syncRecommendations(?Product $product, array $relatedSlugs, $products): void
    {
        if (! $product) {
            return;
        }

        ProductRecommendation::query()
            ->where('product_id', $product->id)
            ->delete();

        foreach ($relatedSlugs as $index => $relatedSlug) {
            $relatedProduct = $products->get($relatedSlug);

            if (! $relatedProduct) {
                continue;
            }

            ProductRecommendation::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'related_product_id' => $relatedProduct->id,
                ],
                [
                    'sort_order' => $index,
                ],
            );
        }
    }
}
