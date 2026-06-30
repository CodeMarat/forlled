<?php

namespace App\Support\Products;

class ProductCategoryNavigationDefaults
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function categories(): array
    {
        return [
            ...self::group('type', [
                ['name' => 'Cleansers', 'slug' => 'cleansers'],
                ['name' => 'Lotions', 'slug' => 'lotions'],
                ['name' => 'Serums', 'slug' => 'serums'],
                ['name' => 'Masks', 'slug' => 'masks'],
                ['name' => 'Creams & Emulsions', 'slug' => 'creams-emulsions'],
                ['name' => 'Eye Care', 'slug' => 'eye-care'],
                ['name' => 'Special Care Products', 'slug' => 'special-care'],
            ], 0),
            ...self::group('skin concern', [
                ['name' => 'Dry Skin', 'slug' => 'dry-skin'],
                ['name' => 'Dehydrated Skin', 'slug' => 'dehydrated-skin'],
                ['name' => 'Uneven Skin Tone', 'slug' => 'uneven-skin-tone'],
                ['name' => 'Blemishes', 'slug' => 'blemishes'],
                ['name' => 'Wrinkles & Fine Lines', 'slug' => 'wrinkles-fine-lines'],
                ['name' => 'Dark Circles', 'slug' => 'dark-circles'],
            ], 100),
            ...self::group('series', [
                ['name' => 'Platinum Line', 'slug' => 'platinum-line'],
                ['name' => 'AC Clear Line', 'slug' => 'ac-clear-line'],
                ['name' => 'BW Line', 'slug' => 'bw-line'],
                ['name' => 'P-Effect Line', 'slug' => 'p-effect-line'],
                ['name' => 'Re-Dify Line', 'slug' => 're-dify-line'],
            ], 200),
            ...self::group('skincare system', [
                ['name' => 'Nourishing Skincare', 'slug' => 'nourishing-skincare'],
                ['name' => 'Moisturising Skincare', 'slug' => 'moisturising-skincare'],
                ['name' => 'Antioxidant Skincare', 'slug' => 'antioxidant-skincare'],
                ['name' => 'Calming Skincare', 'slug' => 'calming-skincare'],
                ['name' => 'Age Control Skincare 35+', 'slug' => 'age-control-skincare-35-plus'],
                ['name' => 'Pigmentation Skincare', 'slug' => 'pigmentation-skincare'],
                ['name' => 'Purifying Skincare', 'slug' => 'purifying-skincare'],
                ['name' => 'Lifting Skincare', 'slug' => 'lifting-skincare'],
                ['name' => 'Eyes Skincare', 'slug' => 'eyes-skincare'],
                ['name' => 'Special Products', 'slug' => 'special-products'],
            ], 300),
        ];
    }

    /**
     * @param  array<int, array{name: string, slug: string}>  $items
     * @return array<int, array<string, mixed>>
     */
    protected static function group(string $groupName, array $items, int $baseOrder): array
    {
        return array_map(
            fn (array $item, int $index): array => [
                'name' => $item['name'],
                'slug' => $item['slug'],
                'group_name' => $groupName,
                'type_label' => mb_strtoupper($groupName),
                'hero_title' => mb_strtoupper($item['name']),
                'hero_image' => null,
                'hero_image_alt' => null,
                'sort_order' => $baseOrder + $index,
                'is_active' => true,
            ],
            $items,
            array_keys($items),
        );
    }
}
