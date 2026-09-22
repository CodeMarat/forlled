<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Products\ProductDetailSections;
use App\Support\Products\ProductType;
use App\Support\Slugs\SlugGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ForlledProductsSeeder extends Seeder
{
    /** @var array<string, string> */
    protected array $categoryAliases = [
        'creams and emulsions' => 'creams & emulsions',
        'special care' => 'special care products',
    ];

    public function run(): void
    {
        $products = $this->loadProducts();
        $statistics = [
            'categories_existing' => 0,
            'categories_added' => [],
            'products_existing' => 0,
            'products_added' => [],
            'products_completed' => 0,
            'category_assignments_added' => 0,
        ];

        DB::transaction(function () use ($products, &$statistics): void {
            $categories = ProductCategory::query()
                ->where('type', ProductType::Product->value)
                ->get()
                ->keyBy(fn (ProductCategory $category): string => $this->normalizeName($category->name));
            $categoryMap = [];

            foreach ($this->uniqueCategoryNames($products) as $categoryName) {
                $category = $this->resolveCategory($categoryName, $categories);
                $categoryMap[$this->normalizeName($categoryName)] = $category;

                if ($category->wasRecentlyCreated) {
                    $statistics['categories_added'][] = $category->name;
                } else {
                    $statistics['categories_existing']++;
                }
            }

            $existingProducts = Product::query()
                ->where('type', ProductType::Product->value)
                ->get()
                ->keyBy(fn (Product $product): string => $this->normalizeName($product->name));
            $categoryPositions = [];

            foreach ($products as $sourceProduct) {
                $normalizedCategory = $this->normalizeName($sourceProduct['category']);
                $category = $categoryMap[$normalizedCategory];
                $normalizedProduct = $this->normalizeName($sourceProduct['name']);
                $existingProduct = $existingProducts->get($normalizedProduct);
                $sortOrder = $categoryPositions[$normalizedCategory] ?? 0;
                $categoryPositions[$normalizedCategory] = $sortOrder + 1;

                if ($existingProduct instanceof Product) {
                    $statistics['products_existing']++;

                    if ($this->completeExistingProduct($existingProduct, $sourceProduct)) {
                        $statistics['products_completed']++;
                    }

                    if (! $existingProduct->productCategories()->whereKey($category->getKey())->exists()) {
                        $existingProduct->productCategories()->attach($category);
                        $statistics['category_assignments_added']++;
                    }

                    continue;
                }

                $product = Product::query()->create($this->productAttributes(
                    $sourceProduct,
                    $sortOrder,
                ));
                $product->productCategories()->attach($category);

                $existingProducts->put($normalizedProduct, $product);
                $statistics['products_added'][] = $product->name;
            }
        });

        $this->report($statistics);
    }

    /** @return array<int, array<string, mixed>> */
    protected function loadProducts(): array
    {
        $path = base_path('forlled_products.json');

        if (! is_file($path)) {
            throw new RuntimeException("Forlle'd products source file does not exist: {$path}");
        }

        $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $products = $payload['products'] ?? null;

        if (! is_array($products)) {
            throw new RuntimeException("Forlle'd products source file must contain a products array.");
        }

        return array_values(array_filter($products, fn (mixed $product): bool => is_array($product)));
    }

    /**
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, string>
     */
    protected function uniqueCategoryNames(array $products): array
    {
        $categories = [];

        foreach ($products as $product) {
            $categoryName = trim((string) ($product['category'] ?? ''));

            if ($categoryName === '') {
                throw new RuntimeException('Every Forlle\'d product must have a category.');
            }

            $categories[$this->normalizeName($categoryName)] ??= $categoryName;
        }

        return array_values($categories);
    }

    /** @param  Collection<string, ProductCategory>  $categories */
    protected function resolveCategory(string $sourceName, Collection $categories): ProductCategory
    {
        $normalizedName = $this->normalizeName($sourceName);
        $lookupName = $this->categoryAliases[$normalizedName] ?? $normalizedName;
        $existingCategory = $categories->get($lookupName) ?? $categories->get($normalizedName);

        if ($existingCategory instanceof ProductCategory) {
            return $existingCategory;
        }

        $name = Str::title(mb_strtolower(trim($sourceName)));
        $category = ProductCategory::query()->create([
            'name' => $name,
            'slug' => SlugGenerator::uniqueFromParts(ProductCategory::class, [$name]),
            'group_name' => 'type',
            'type' => ProductType::Product->value,
            'type_label' => 'TYPE',
            'hero_title' => mb_strtoupper($name),
            'hero_image' => null,
            'sort_order' => $this->nextCategorySortOrder(),
            'is_active' => true,
        ]);

        $categories->put($normalizedName, $category);

        return $category;
    }

    protected function nextCategorySortOrder(): int
    {
        return ((int) ProductCategory::query()
            ->where('group_name', 'type')
            ->max('sort_order')) + 1;
    }

    /**
     * @param  array<string, mixed>  $sourceProduct
     * @return array<string, mixed>
     */
    protected function productAttributes(array $sourceProduct, int $sortOrder): array
    {
        $name = trim((string) ($sourceProduct['name'] ?? ''));
        $description = trim((string) ($sourceProduct['description'] ?? ''));

        if ($name === '' || $description === '') {
            throw new RuntimeException('Every Forlle\'d product must have a name and description.');
        }

        return [
            'name' => $name,
            'slug' => SlugGenerator::uniqueFromParts(Product::class, [$name]),
            'type' => ProductType::Product->value,
            'description' => $this->paragraph($description),
            'listing_description' => null,
            'size' => $this->nullableString($sourceProduct['size_or_packaging'] ?? null),
            'hero_image' => null,
            'side_image' => null,
            'key_benefits' => $this->benefits($sourceProduct['key_benefits'] ?? []),
            'detail_sections' => $this->detailSections($sourceProduct),
            'recommendations_title' => null,
            'combine_with_title' => null,
            'combine_left_title' => null,
            'combine_left_text' => null,
            'combine_right_title' => null,
            'combine_right_text' => null,
            'is_favorite' => false,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ];
    }

    /** @param  array<string, mixed>  $sourceProduct */
    protected function completeExistingProduct(Product $product, array $sourceProduct): bool
    {
        $attributes = [];

        if (blank($product->description) && filled($sourceProduct['description'] ?? null)) {
            $attributes['description'] = $this->paragraph((string) $sourceProduct['description']);
        }

        if (blank($product->size) && filled($sourceProduct['size_or_packaging'] ?? null)) {
            $attributes['size'] = $this->nullableString($sourceProduct['size_or_packaging']);
        }

        $keyBenefits = $this->mergeBenefits(
            is_array($product->key_benefits) ? $product->key_benefits : [],
            $this->benefits($sourceProduct['key_benefits'] ?? []),
        );

        if ($keyBenefits !== ($product->key_benefits ?? [])) {
            $attributes['key_benefits'] = $keyBenefits;
        }

        $detailSections = $this->mergeDetailSections(
            is_array($product->detail_sections) ? $product->detail_sections : [],
            $this->detailSections($sourceProduct),
        );

        if ($detailSections !== ($product->detail_sections ?? [])) {
            $attributes['detail_sections'] = $detailSections;
        }

        if ($attributes === []) {
            return false;
        }

        $product->update($attributes);

        return true;
    }

    /**
     * @param  array<int, mixed>  $existingBenefits
     * @param  array<int, array{benefit: string}>  $sourceBenefits
     * @return array<int, mixed>
     */
    protected function mergeBenefits(array $existingBenefits, array $sourceBenefits): array
    {
        $mergedBenefits = array_values($existingBenefits);
        $existingNames = collect($existingBenefits)
            ->map(fn (mixed $benefit): string => $this->normalizeName(
                is_array($benefit) ? (string) ($benefit['benefit'] ?? '') : (string) $benefit,
            ))
            ->filter()
            ->flip();

        foreach ($sourceBenefits as $sourceBenefit) {
            $normalizedBenefit = $this->normalizeName($sourceBenefit['benefit']);

            if ($existingNames->has($normalizedBenefit)) {
                continue;
            }

            $mergedBenefits[] = $sourceBenefit;
            $existingNames->put($normalizedBenefit, true);
        }

        return $mergedBenefits;
    }

    /**
     * @param  array<int, mixed>  $existingSections
     * @param  array<int, mixed>  $sourceSections
     * @return array<int, mixed>
     */
    protected function mergeDetailSections(array $existingSections, array $sourceSections): array
    {
        $mergedSections = array_map(
            fn (mixed $section): mixed => $this->alignIngredientTableCells($section),
            array_values($existingSections),
        );
        $existingTitles = collect($existingSections)
            ->filter(fn (mixed $section): bool => is_array($section))
            ->map(fn (array $section): string => $this->normalizeName((string) ($section['title'] ?? '')))
            ->filter()
            ->flip();

        foreach ($sourceSections as $sourceSection) {
            if (! is_array($sourceSection)) {
                continue;
            }

            $normalizedTitle = $this->normalizeName((string) ($sourceSection['title'] ?? ''));

            if ($normalizedTitle === '' || $existingTitles->has($normalizedTitle)) {
                continue;
            }

            $mergedSections[] = $sourceSection;
            $existingTitles->put($normalizedTitle, true);
        }

        $ingredientsIndex = null;
        $howToUseIndex = null;

        foreach ($mergedSections as $index => $section) {
            if (! is_array($section)) {
                continue;
            }

            $title = $this->normalizeName((string) ($section['title'] ?? ''));

            if ($title === 'active ingredients' && $ingredientsIndex === null) {
                $ingredientsIndex = $index;
            } elseif ($title === 'how to use' && $howToUseIndex === null) {
                $howToUseIndex = $index;
            }
        }

        if ($ingredientsIndex !== null && $howToUseIndex !== null && $ingredientsIndex > $howToUseIndex) {
            [$ingredientsSection] = array_splice($mergedSections, $ingredientsIndex, 1);
            array_splice($mergedSections, $howToUseIndex, 0, [$ingredientsSection]);
        }

        return $mergedSections;
    }

    protected function alignIngredientTableCells(mixed $section): mixed
    {
        if (
            ! is_array($section)
            || $this->normalizeName((string) ($section['title'] ?? '')) !== 'active ingredients'
            || ! is_string($section['content'] ?? null)
        ) {
            return $section;
        }

        $section['content'] = str_replace(
            '<td>',
            '<td style="vertical-align: top;">',
            $section['content'],
        );

        return $section;
    }

    /**
     * @param  array<string, mixed>  $sourceProduct
     * @return array<int, array{title: string, content: string, is_visible: bool}>
     */
    protected function detailSections(array $sourceProduct): array
    {
        $sections = [
            ['title' => 'Indications', 'content' => $this->list($sourceProduct['indications'] ?? [])],
            ['title' => 'Product density', 'content' => $this->paragraph((string) ($sourceProduct['product_density'] ?? ''))],
            ['title' => 'Active ingredients', 'content' => $this->activeIngredients($sourceProduct)],
            ['title' => 'How to use', 'content' => $this->list($sourceProduct['how_to_use'] ?? [], ordered: true)],
        ];

        return ProductDetailSections::makeVisible(array_values(array_filter(
            $sections,
            fn (array $section): bool => filled($section['content']),
        )));
    }

    /** @return array<int, array{benefit: string}> */
    protected function benefits(mixed $benefits): array
    {
        if (! is_array($benefits)) {
            return [];
        }

        return collect($benefits)
            ->filter(fn (mixed $benefit): bool => is_string($benefit) && filled(trim($benefit)))
            ->map(fn (string $benefit): array => ['benefit' => trim($benefit)])
            ->values()
            ->all();
    }

    /** @param  array<string, mixed>  $sourceProduct */
    protected function activeIngredients(array $sourceProduct): string
    {
        $table = $sourceProduct['active_ingredients_table'] ?? null;

        if (is_array($table) && $table !== []) {
            $rows = collect($table)
                ->filter(fn (mixed $row): bool => is_array($row))
                ->map(function (array $row): string {
                    $cells = collect($row)
                        ->filter(fn (mixed $cell): bool => is_string($cell) && filled(trim($cell)))
                        ->map(fn (string $cell): string => '<td style="vertical-align: top;">'.$this->ingredientCell($cell).'</td>')
                        ->implode('');

                    return $cells === '' ? '' : "<tr>{$cells}</tr>";
                })
                ->filter()
                ->implode('');

            if ($rows !== '') {
                return "<table><tbody>{$rows}</tbody></table>";
            }
        }

        $groups = $sourceProduct['active_ingredients'] ?? null;

        if (! is_array($groups)) {
            return '';
        }

        return collect($groups)
            ->filter(fn (mixed $group): bool => is_array($group))
            ->map(function (array $group): string {
                $title = trim((string) ($group['group'] ?? ''));

                return '<p><strong>'.e($title).'</strong></p>'.$this->list($group['ingredients'] ?? []);
            })
            ->implode('');
    }

    protected function ingredientCell(string $cell): string
    {
        $lines = preg_split('/\R/u', trim($cell)) ?: [];
        $title = array_shift($lines);

        return '<p><strong>'.e((string) $title).'</strong></p>'.$this->list($lines);
    }

    protected function paragraph(string $value): string
    {
        $value = trim($value);

        return $value === '' ? '' : '<p>'.e($value).'</p>';
    }

    protected function list(mixed $items, bool $ordered = false): string
    {
        if (! is_array($items)) {
            return '';
        }

        $content = collect($items)
            ->filter(fn (mixed $item): bool => is_string($item) && filled(trim($item)))
            ->map(fn (string $item): string => '<li>'.e(trim($item)).'</li>')
            ->implode('');

        if ($content === '') {
            return '';
        }

        $tag = $ordered ? 'ol' : 'ul';

        return "<{$tag}>{$content}</{$tag}>";
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value) || blank(trim($value))) {
            return null;
        }

        return trim($value);
    }

    protected function normalizeName(string $name): string
    {
        return mb_strtolower(trim($name));
    }

    /**
     * @param  array{categories_existing: int, categories_added: array<int, string>, products_existing: int, products_added: array<int, string>, products_completed: int, category_assignments_added: int}  $statistics
     */
    protected function report(array $statistics): void
    {
        if (! $this->command) {
            return;
        }

        $this->command->table(['Result', 'Count'], [
            ['Categories already existed', $statistics['categories_existing']],
            ['Categories added', count($statistics['categories_added'])],
            ['Products already existed', $statistics['products_existing']],
            ['Products added', count($statistics['products_added'])],
            ['Existing products completed', $statistics['products_completed']],
            ['Category assignments added', $statistics['category_assignments_added']],
        ]);

        if ($statistics['categories_added'] !== []) {
            $this->command->line('Added categories: '.implode(', ', $statistics['categories_added']));
        }

        if ($statistics['products_added'] !== []) {
            $this->command->line('Added products: '.implode(', ', $statistics['products_added']));
        }
    }
}
