<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Products\ProductDetailSections;
use App\Support\Products\ProductType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProfessionalProductContentSeeder extends ForlledProductsSeeder
{
    public function run(): void
    {
        $sourceProducts = $this->loadProfessionalProducts();

        DB::transaction(function () use ($sourceProducts): void {
            $copyIds = DB::table('professional_product_copies')->pluck('treatment_product_id');
            $products = Product::query()
                ->where('type', ProductType::Treatment->value)
                ->whereIn('id', $copyIds)
                ->with('productCategories')
                ->lockForUpdate()
                ->get();
            $categories = ProductCategory::query()
                ->where('type', ProductType::Treatment->value)
                ->get();

            if ($products->count() !== count($sourceProducts) || $copyIds->count() !== count($sourceProducts)) {
                throw new RuntimeException('Professional product copies do not match the document; no content was changed.');
            }

            $assignments = [];

            foreach ($sourceProducts as $source) {
                $matches = $products->filter(fn (Product $product): bool => $this->normalizeProfessionalName($product->name)
                    === $this->normalizeProfessionalName((string) $source['name']));

                if ($matches->count() !== 1) {
                    throw new RuntimeException("Expected one professional copy for {$source['name']}; found {$matches->count()}.");
                }

                $product = $matches->first();
                $matchingCategory = $categories->first(fn (ProductCategory $category): bool => $this->normalizeProfessionalName($category->name)
                    === $this->normalizeProfessionalName((string) $source['category']));

                if (! $matchingCategory || $product->productCategories->count() !== 1
                    || $product->productCategories->first()->getKey() !== $matchingCategory->getKey()) {
                    throw new RuntimeException("Professional category mismatch for {$source['name']}; no content was changed.");
                }

                if (isset($assignments[$product->getKey()])) {
                    throw new RuntimeException("Duplicate professional document entry for {$source['name']}.");
                }

                $assignments[$product->getKey()] = [
                    'product' => $product,
                    'content' => $this->professionalContent($source),
                ];
            }

            $updated = 0;

            foreach ($assignments as $productId => $assignment) {
                $product = $assignment['product'];
                $content = $assignment['content'];

                DB::table('professional_product_content_backups')->insertOrIgnore([
                    'product_id' => $productId,
                    'original_content' => json_encode(Arr::only($product->getRawOriginal(), array_keys($content)), JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($this->matchesContent($product, $content)) {
                    continue;
                }

                DB::table('products')->where('id', $productId)->update([
                    ...$this->databaseContent($content),
                    'updated_at' => now(),
                ]);
                $updated++;
            }

            foreach ($assignments as $productId => $assignment) {
                $actual = Product::query()->findOrFail($productId);

                if (! $this->matchesContent($actual, $assignment['content'])) {
                    throw new RuntimeException("Professional content did not persist for {$actual->name}; all changes were rolled back.");
                }
            }

            $this->command?->info('Professional content: '.count($assignments)
                ." products matched; {$updated} updated; ".(count($assignments) - $updated).' unchanged.');
        });
    }

    /** @return array<int, array<string, mixed>> */
    protected function loadProfessionalProducts(): array
    {
        $path = base_path('forlled_prof_products.json');

        if (! is_file($path)) {
            throw new RuntimeException("Professional product content source is missing: {$path}");
        }

        $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $products = $payload['products'] ?? null;

        if (! is_array($products) || $products === []) {
            throw new RuntimeException('Professional product content source must contain products.');
        }

        foreach ($products as $source) {
            if (! is_array($source) || blank($source['name'] ?? null) || blank($source['category'] ?? null)
                || blank($source['size_or_packaging'] ?? null) || blank($source['description'] ?? null)
                || empty($source['key_benefits']) || empty($source['indications'])
                || empty($source['active_ingredients_table'])) {
                throw new RuntimeException('Professional document contains an incomplete product.');
            }
        }

        return array_values($products);
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function professionalContent(array $source): array
    {
        $description = collect(preg_split('/\n{2,}/u', trim((string) $source['description'])) ?: [])
            ->map(fn (string $paragraph): string => $this->paragraph($paragraph))
            ->implode('');

        $rows = collect($source['active_ingredients_table'])
            ->map(function (array $row): string {
                if (count($row) !== 2) {
                    throw new RuntimeException('Professional ingredient table must have two columns.');
                }

                $cells = collect($row)
                    ->map(fn (string $cell): string => '<td style="vertical-align: top;">'
                        .(trim($cell) === '' ? '' : $this->ingredientCell($cell)).'</td>')
                    ->implode('');

                return "<tr>{$cells}</tr>";
            })
            ->implode('');

        return [
            'description' => $description,
            'listing_description' => null,
            'size' => trim((string) $source['size_or_packaging']),
            'key_benefits' => $this->benefits($source['key_benefits']),
            'detail_sections' => ProductDetailSections::makeVisible([
                ['title' => 'Indications', 'content' => $this->list($source['indications'])],
                ['title' => 'Active ingredients', 'content' => "<table><tbody>{$rows}</tbody></table>"],
            ]),
            'recommendations_title' => null,
            'combine_with_title' => null,
            'combine_left_title' => null,
            'combine_left_text' => null,
            'combine_right_title' => null,
            'combine_right_text' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function databaseContent(array $content): array
    {
        $content['key_benefits'] = json_encode($content['key_benefits'], JSON_THROW_ON_ERROR);
        $content['detail_sections'] = json_encode($content['detail_sections'], JSON_THROW_ON_ERROR);

        return $content;
    }

    /** @param  array<string, mixed>  $content */
    private function matchesContent(Product $product, array $content): bool
    {
        foreach ($content as $field => $expected) {
            $actual = $product->getAttribute($field);

            if (in_array($field, ['key_benefits', 'detail_sections'], true)) {
                if ($actual != $expected) {
                    return false;
                }

                continue;
            }

            if ($actual !== $expected) {
                return false;
            }
        }

        return true;
    }

    private function normalizeProfessionalName(string $name): string
    {
        $name = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name) ?? $name));

        return preg_replace('/\b(ha|pl|em|vc|ce)\s+100\b/u', '${1}100', $name) ?? $name;
    }
}
