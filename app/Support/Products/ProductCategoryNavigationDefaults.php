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
                'sort_order' => $baseOrder + $index,
                'is_active' => true,
            ],
            $items,
            array_keys($items),
        );
    }
}
