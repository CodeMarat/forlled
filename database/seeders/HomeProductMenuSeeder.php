<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Products\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class HomeProductMenuSeeder extends Seeder
{
    /** @var array<string, array<string, array<int, string>>> */
    private const MENU = [
        'type' => [
            'Cleansers' => ['Hyalogy Remover for point make-up', 'Hyalogy P-effect clearance cleansing', 'Hyalogy P-effect re-purerance wash', 'Hyalogy creamy wash'],
            'Lotions' => ['Hyalogy P-effect refining lotion', 'Hyalogy platinum lotion', 'Hyalogy AC clear lotion', 'Hyalogy Re-Dify lotion', 'Hyalogy SensiSkin lotion'],
            'Serums' => ['Hyalogy P-effect essence', 'Hyalogy platinum essence', 'Hyalogy FH Essence', 'Hyalogy AC clear essence', "Hyalogy \u{03B1}", "Hyalogy \u{03B2}", 'Hyalogy AC spot essence', 'Hyalogy C20 essence', 'Hyalogy SensiSkin essence', 'Hyalogy CLG essence'],
            'Creams and emulsions' => ['Hyalogy P-effect reliance gel', 'Hyalogy P-effect basing emulsion', 'Hyalogy P-effect nourishing cream', 'Hyalogy VCIP cream', 'Hyalogy Lift cream', 'Hyalogy Platinum Face Cream', 'Hyalogy AC clear cream', 'Hyalogy AC clear mattifier', 'Hyalogy BW night cream', 'Hyalogy Re-Dify cream', 'Hyalogy SensiSkin cream', 'Hyalogy CLG cream'],
            'Masks' => ['Hyalogy emollient cream pack', 'Hyalogy PD cream pack', 'Hyalogy BW intense mask', 'Hyalogy Sparkling gel pack', 'Hyalogy CLG Face Mask'],
            'Eyes, lips and neck care' => ['Hyalogy daily and nightly cream for eyes', 'Hyalogy Platinum eye cream', 'Hyalogy P-effect sheet', 'Hyalogy Re-Dify eye mask', 'Hyalogy Eye Moistlift', 'Hyalogy protective cream for lips', 'Neck and Decollete essence', 'Neck and Decollete cream'],
            'Sun care' => ['Hyalogy UV Intense protector (SPF 50)'],
            'Special care' => ['Hyalogy peeling lotion', 'Hyalogy body treatment cream'],
        ],
        'skin concern' => [
            'early aging' => ['Hyalogy CLG essence', 'Hyalogy CLG cream', 'Hyalogy CLG Face Mask', 'Hyalogy Lift cream', 'Hyalogy platinum lotion', 'Hyalogy platinum essence', 'Hyalogy Platinum Face Cream', 'Hyalogy Platinum eye cream', 'Neck and Decollete essence', 'Neck and Decollete cream'],
            'advanced aging' => ['Hyalogy Re-Dify lotion', 'Hyalogy Re-Dify cream', 'Hyalogy Re-Dify eye mask', 'Hyalogy FH Essence', 'Hyalogy PD cream pack', "Hyalogy \u{03B1}", "Hyalogy \u{03B2}", 'Hyalogy platinum lotion', 'Hyalogy platinum essence', 'Hyalogy Platinum Face Cream', 'Hyalogy Platinum eye cream', 'Neck and Decollete essence', 'Neck and Decollete cream'],
            'dry & dehydrated skin' => ['Hyalogy P-effect clearance cleansing', 'Hyalogy P-effect re-purerance wash', 'Hyalogy P-effect refining lotion', 'Hyalogy P-effect essence', 'Hyalogy P-effect reliance gel', 'Hyalogy P-effect basing emulsion', 'Hyalogy P-effect nourishing cream', 'Hyalogy P-effect sheet'],
            'sensitive skin' => ['Hyalogy SensiSkin lotion', 'Hyalogy SensiSkin essence', 'Hyalogy SensiSkin cream'],
            'uneven skin tone & pigmentation' => ['Hyalogy platinum lotion', 'Hyalogy platinum essence', 'Hyalogy Platinum Face Cream', 'Hyalogy Platinum eye cream', 'Hyalogy VCIP cream', 'Hyalogy C20 essence', 'Hyalogy BW night cream', 'Hyalogy BW intense mask'],
            'acne & blemishes' => ['Hyalogy AC clear lotion', 'Hyalogy AC clear essence', 'Hyalogy AC spot essence', 'Hyalogy AC clear cream', 'Hyalogy AC clear mattifier', 'Hyalogy peeling lotion'],
            'dark circles & eye bags' => ['Hyalogy daily and nightly cream for eyes', 'Hyalogy Platinum eye cream', 'Hyalogy P-effect sheet', 'Hyalogy Re-Dify eye mask', 'Hyalogy Eye Moistlift'],
        ],
        'collection' => [
            'platinum line' => ['Hyalogy platinum lotion', 'Hyalogy platinum essence', 'Hyalogy Platinum Face Cream', 'Hyalogy Platinum eye cream'],
            'ac clear line' => ['Hyalogy AC clear lotion', 'Hyalogy AC clear essence', 'Hyalogy AC spot essence', 'Hyalogy AC clear cream', 'Hyalogy AC clear mattifier'],
            'clg line' => ['Hyalogy CLG essence', 'Hyalogy CLG cream', 'Hyalogy CLG Face Mask'],
            're-dify line' => ['Hyalogy Re-Dify lotion', 'Hyalogy Re-Dify cream', 'Hyalogy Re-Dify eye mask'],
            'bw line' => ['Hyalogy BW night cream', 'Hyalogy BW intense mask'],
            'sensiskin line' => ['Hyalogy SensiSkin lotion', 'Hyalogy SensiSkin essence', 'Hyalogy SensiSkin cream'],
            'p-effect products' => ['Hyalogy P-effect clearance cleansing', 'Hyalogy P-effect re-purerance wash', 'Hyalogy P-effect refining lotion', 'Hyalogy P-effect essence', 'Hyalogy P-effect reliance gel', 'Hyalogy P-effect basing emulsion', 'Hyalogy P-effect nourishing cream', 'Hyalogy P-effect sheet'],
        ],
    ];

    /** @var array<string, string> */
    private const CATEGORY_ALIASES = [
        'creams and emulsions' => 'creams & emulsions',
        'eyes, lips and neck care' => 'eyes, lips & neck care',
    ];

    /** @var array<string, string> */
    private const PRODUCT_ALIASES = [
        'neck and decollete essence' => 'hyalogy neck and decollete essence',
        'neck and decollete cream' => 'hyalogy neck and decollete cream',
    ];

    public function run(): void
    {
        DB::transaction(fn () => $this->reconcile());
    }

    private function reconcile(): void
    {
        $categories = ProductCategory::query()->where('type', ProductType::Product->value)->lockForUpdate()->get();
        $products = Product::query()->where('type', ProductType::Product->value)->lockForUpdate()->get();
        $expected = [];
        $categoryIds = [];
        $categoryOrder = [];

        foreach ($this->menu() as $group => $categoryProducts) {
            foreach ($categoryProducts as $categoryName => $names) {
                $categoryLookup = self::CATEGORY_ALIASES[$this->normalize($categoryName)] ?? $this->normalize($categoryName);
                $matchingCategories = $categories->filter(fn (ProductCategory $category): bool => $category->group_name === $group
                    && $this->normalize($category->name) === $categoryLookup);

                if ($matchingCategories->count() !== 1) {
                    throw new RuntimeException("Expected one ordinary category for {$group} / {$categoryName}; found {$matchingCategories->count()}.");
                }

                $category = $matchingCategories->first();

                if (blank($category->slug)) {
                    throw new RuntimeException("Ordinary category {$categoryName} must have a slug; no assignments were changed.");
                }

                $categoryIds[$category->getKey()] = true;
                $categoryOrder[$category->getKey()] = [
                    'name' => $categoryLookup,
                    'sort_order' => count($categoryOrder) + 1,
                ];

                foreach ($names as $name) {
                    $productLookup = self::PRODUCT_ALIASES[$this->normalize($name)] ?? $this->normalize($name);
                    $matchingProducts = $products->filter(fn (Product $product): bool => $this->normalize($product->name) === $productLookup);

                    if ($matchingProducts->count() !== 1) {
                        throw new RuntimeException("Expected one ordinary product for {$name}; found {$matchingProducts->count()}.");
                    }

                    $product = $matchingProducts->first();

                    if (! $product->is_active || blank($product->slug)) {
                        throw new RuntimeException("Ordinary product {$name} must be active and have a slug; no assignments were changed.");
                    }

                    $key = $product->getKey().':'.$category->getKey();

                    if (isset($expected[$key])) {
                        throw new RuntimeException("Duplicate menu assignment for {$name} in {$categoryName}.");
                    }

                    $expected[$key] = [
                        'product_id' => $product->getKey(),
                        'product_category_id' => $category->getKey(),
                    ];
                }
            }
        }

        if ($categories->count() !== count($categoryIds)) {
            throw new RuntimeException('Ordinary categories differ from the home menu; no assignments were changed.');
        }

        $existing = DB::table('product_category_product')
            ->whereIn('product_category_id', array_keys($categoryIds))
            ->lockForUpdate()
            ->get(['product_id', 'product_category_id'])
            ->keyBy(fn (object $row): string => $row->product_id.':'.$row->product_category_id);

        $ordinaryProductIds = array_fill_keys($products->modelKeys(), true);

        if ($existing->contains(fn (object $row): bool => ! isset($ordinaryProductIds[$row->product_id]))) {
            throw new RuntimeException('An ordinary category is linked to a non-ordinary product; no assignments were changed.');
        }

        $missing = array_diff_key($expected, $existing->all());
        $unexpected = array_diff_key($existing->all(), $expected);

        if (! DB::table('home_product_menu_backups')->where('id', 1)->exists()) {
            DB::table('home_product_menu_backups')->insert([
                'id' => 1,
                'categories_snapshot' => json_encode($categories->map->getAttributes()->all(), JSON_THROW_ON_ERROR),
                'links_snapshot' => json_encode(DB::table('product_category_product')
                    ->whereIn('product_category_id', array_keys($categoryIds))
                    ->get()->all(), JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($categories as $category) {
            $desired = $categoryOrder[$category->getKey()];

            if ($category->name !== $desired['name'] || $category->sort_order !== $desired['sort_order'] || ! $category->is_active) {
                DB::table('product_categories')->where('id', $category->getKey())->update([
                    ...$desired,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($unexpected as $row) {
            $deleted = DB::table('product_category_product')
                ->where('product_id', $row->product_id)
                ->where('product_category_id', $row->product_category_id)
                ->delete();

            if ($deleted !== 1) {
                throw new RuntimeException('A menu assignment changed during reconciliation; all changes were rolled back.');
            }
        }

        if ($missing !== []) {
            $now = now();
            DB::table('product_category_product')->insert(
                array_map(fn (array $pair): array => [
                    ...$pair,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], array_values($missing)),
            );
        }

        $actual = DB::table('product_category_product')
            ->whereIn('product_category_id', array_keys($categoryIds))
            ->get(['product_id', 'product_category_id'])
            ->keyBy(fn (object $row): string => $row->product_id.':'.$row->product_category_id);

        if (array_diff_key($actual->all(), $expected) !== [] || array_diff_key($expected, $actual->all()) !== []) {
            throw new RuntimeException('Home menu assignments did not match the workbook; all changes were rolled back.');
        }

        $navigation = DB::table('product_categories')
            ->where('type', ProductType::Product->value)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        if ($navigation->pluck('id')->map(fn (int $id): int => $id)->all() !== array_keys($categoryOrder)
            || $navigation->contains(fn (object $category): bool => $category->name !== $categoryOrder[$category->id]['name'])) {
            throw new RuntimeException('Home menu categories did not match the workbook; all changes were rolled back.');
        }

        $this->command?->info('Home menu assignments: '.count($expected)
            .'; added: '.count($missing)
            .'; already present: '.(count($expected) - count($missing))
            .'; removed extra links: '.count($unexpected).'.');
    }

    /** @return array<string, array<string, array<int, string>>> */
    protected function menu(): array
    {
        return self::MENU;
    }

    private function normalize(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', Str::ascii($name)) ?? $name));
    }
}
