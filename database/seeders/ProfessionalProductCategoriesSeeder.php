<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Support\Products\ProductType;
use App\Support\Slugs\SlugGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProfessionalProductCategoriesSeeder extends Seeder
{
    /** @var array<int, array{name: string, aliases: array<int, string>}> */
    private const CATEGORIES = [
        ['name' => 'Cleansers', 'aliases' => []],
        ['name' => 'Lotions', 'aliases' => []],
        ['name' => 'Serums', 'aliases' => []],
        ['name' => 'Biopure professional serums', 'aliases' => []],
        ['name' => 'Creams and emulsions', 'aliases' => ['Creams & Emulsions']],
        ['name' => 'Masks', 'aliases' => []],
        ['name' => 'Eyes and lips care', 'aliases' => ['Eye Care']],
        ['name' => 'Sun care', 'aliases' => []],
        ['name' => 'Special care', 'aliases' => ['Special Care Products']],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $existingCategories = ProductCategory::query()
                ->where('type', ProductType::Treatment->value)
                ->get();

            foreach (self::CATEGORIES as $sortOrder => $definition) {
                $name = $definition['name'];
                $names = array_map(
                    $this->normalizeName(...),
                    [$name, ...$definition['aliases']],
                );
                $matches = $existingCategories->filter(
                    fn (ProductCategory $category): bool => in_array($this->normalizeName($category->name), $names, true),
                );

                if ($matches->count() > 1) {
                    throw new RuntimeException("Multiple treatment categories match {$name}: ".$matches->pluck('id')->implode(', '));
                }

                $category = $matches->first();

                if ($category instanceof ProductCategory) {
                    $attributes = [
                        'name' => $name,
                        'group_name' => 'type',
                        'sort_order' => $sortOrder,
                    ];

                    if (mb_strtoupper(trim($category->hero_title)) === mb_strtoupper(trim($category->name))) {
                        $attributes['hero_title'] = mb_strtoupper($name);
                    }

                    $category->fill($attributes);

                    if ($category->isDirty()) {
                        $category->save();
                    }

                    continue;
                }

                $existingCategories->push(ProductCategory::query()->create([
                    'name' => $name,
                    'slug' => SlugGenerator::uniqueFromParts(ProductCategory::class, [$name, 'treatment']),
                    'group_name' => 'type',
                    'type' => ProductType::Treatment->value,
                    'type_label' => 'TYPE',
                    'hero_title' => mb_strtoupper($name),
                    'hero_image' => null,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ]));
            }
        });
    }

    private function normalizeName(string $name): string
    {
        return mb_strtolower(trim($name));
    }
}
