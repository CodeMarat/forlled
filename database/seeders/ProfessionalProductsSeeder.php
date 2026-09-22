<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Products\ProductType;
use App\Support\Slugs\SlugGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProfessionalProductsSeeder extends Seeder
{
    /** @var array<string, array<int, string>> */
    private const PRODUCTS = [
        'Cleansers' => ['Hyalogy Remover for point make-up', 'Hyalogy P-effect clearance cleansing', 'Hyalogy P-effect re-purerance wash'],
        'Lotions' => ['Hyalogy P-effect refining lotion', 'Hyalogy Purifying lotion', 'Hyalogy Platinum lotion'],
        'Serums' => ['Hyalogy P-effect essence', 'Hyalogy platinum essence', 'Hyalogy essence powder'],
        'Biopure professional serums' => ['Biopure HA100 essence', 'Biopure PL100 essence', 'Biopure EM100 essence', 'Biopure VC100 essence', 'Biopure CE100 essence'],
        'Creams and emulsions' => ['Hyalogy P-effect reliance gel', 'Hyalogy P-effect basing emulsion', 'Hyalogy P-effect nourishing cream', 'Hyalogy Platinum Face Cream', 'Hyalogy P-effect deep moisturizer'],
        'Masks' => ['Hyalogy PT gel mask', 'Hyalogy Royal clay pack', 'Hyalogy Oxygenic gel pack', 'Hyalogy Lift mask', 'Hyalogy Deep purifying mask', 'Hyalogy SensiSkin mask', 'Hyalogy Re-Dify face mask'],
        'Eyes and lips care' => ['Hyalogy daily and nightly cream for eyes', 'Hyalogy Platinum eye cream', 'Hyalogy P-effect sheet', 'Hyalogy Re-Dify eye mask', 'Hyalogy Eye Moistlift', 'Hyalogy protective cream for lips'],
        'Sun care' => ['Hyalogy UV Intense protector (SPF 50)'],
        'Special care' => ['Hyalogy peeling lotion', 'Hyalogy body treatment cream'],
    ];

    public function run(): void
    {
        $categories = ProductCategory::query()->where('type', ProductType::Treatment->value)->get();
        $sources = Product::query()->where('type', ProductType::Product->value)->get();
        $assignments = [];

        foreach ($this->products() as $categoryName => $productNames) {
            $category = $categories->first(fn (ProductCategory $item): bool => $this->normalize($item->name) === $this->normalize($categoryName));

            if (! $category) {
                throw new RuntimeException("Missing treatment category: {$categoryName}. Run ProfessionalProductCategoriesSeeder first.");
            }

            foreach ($productNames as $position => $name) {
                $matches = $sources->filter(fn (Product $item): bool => $this->normalize($item->name) === $this->normalize($name));

                if ($matches->count() !== 1) {
                    throw new RuntimeException("Expected one ordinary product for {$name}; found {$matches->count()}.");
                }

                $assignments[] = [$matches->first(), $category, $position];
            }
        }

        $existingCopies = DB::table('professional_product_copies')->pluck('treatment_product_id', 'source_product_id');
        $treatments = Product::query()->where('type', ProductType::Treatment->value)->get();

        foreach ($assignments as [$source]) {
            if ($existingCopies->has($source->getKey())) {
                continue;
            }

            if ($treatments->contains(fn (Product $item): bool => $this->normalize($item->name) === $this->normalize($source->name))) {
                throw new RuntimeException("Treatment product already exists without an import mapping: {$source->name}.");
            }

            foreach (['hero_image', 'side_image'] as $attribute) {
                $path = $source->{$attribute};

                if (filled($path) && ! filter_var($path, FILTER_VALIDATE_URL) && ! Storage::disk('public')->exists(ltrim($path, '/'))) {
                    throw new RuntimeException("Source media is missing: {$path}");
                }
            }
        }

        $created = 0;

        foreach ($assignments as [$source, $category, $position]) {
            $mappedId = $existingCopies->get($source->getKey());

            if ($mappedId !== null) {
                if (! Product::query()->whereKey($mappedId)->where('type', ProductType::Treatment->value)->exists()) {
                    throw new RuntimeException("Invalid professional copy for product {$source->getKey()}.");
                }

                continue;
            }

            $directory = 'products/items/professional/'.Str::uuid();

            try {
                $media = [
                    'hero_image' => $this->copyMedia($source->hero_image, "{$directory}/hero"),
                    'side_image' => $this->copyMedia($source->side_image, "{$directory}/side"),
                ];

                DB::transaction(function () use ($source, $category, $position, $media): void {
                    $copy = $source->replicate();
                    $copy->forceFill([
                        'slug' => SlugGenerator::uniqueFromParts(Product::class, [$source->slug, 'professional']),
                        'type' => ProductType::Treatment->value,
                        'sort_order' => $position,
                        ...$media,
                    ]);
                    $copy->save();
                    $copy->productCategories()->attach($category);
                    DB::table('professional_product_copies')->insert([
                        'source_product_id' => $source->getKey(),
                        'treatment_product_id' => $copy->getKey(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
                $created++;
            } catch (\Throwable $exception) {
                Storage::disk('public')->deleteDirectory($directory);
                throw $exception;
            }
        }

        $copyIds = DB::table('professional_product_copies')->pluck('treatment_product_id', 'source_product_id');

        foreach ($assignments as [$source]) {
            $copy = Product::query()->findOrFail($copyIds->get($source->getKey()));

            foreach ($source->productRecommendations as $recommendation) {
                $relatedCopyId = $copyIds->get($recommendation->related_product_id);

                if ($relatedCopyId && ! $copy->productRecommendations()->where('related_product_id', $relatedCopyId)->exists()) {
                    $copy->productRecommendations()->create([
                        'related_product_id' => $relatedCopyId,
                        'sort_order' => $recommendation->sort_order,
                    ]);
                }
            }
        }

        $this->command?->info("Professional products created: {$created}; already copied: ".(count($assignments) - $created).'.');
    }

    /** @return array<string, array<int, string>> */
    protected function products(): array
    {
        return self::PRODUCTS;
    }

    private function normalize(string $name): string
    {
        $name = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name) ?? $name));

        return preg_replace('/\b(ha|pl|em|vc|ce)\s+100\b/u', '${1}100', $name) ?? $name;
    }

    private function copyMedia(?string $path, string $destination): ?string
    {
        if (blank($path) || filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $disk = Storage::disk('public');
        $path = ltrim($path, '/');

        if (! $disk->exists($path)) {
            throw new RuntimeException("Source media is missing: {$path}");
        }

        $sourceDirectory = dirname($path);
        $filename = basename($path);
        $targetPath = "{$destination}/{$filename}";
        $this->copyFile($path, $targetPath);

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'm3u8') {
            if ($sourceDirectory === 'products/items') {
                throw new RuntimeException("HLS playlist must be in its own directory: {$path}");
            }

            foreach ($disk->allFiles($sourceDirectory) as $file) {
                if ($file !== $path) {
                    $this->copyFile($file, $destination.'/'.substr($file, strlen($sourceDirectory) + 1));
                }
            }
        } else {
            $variantDirectory = $sourceDirectory.'/'.trim((string) config('image_pipeline.variants_directory'), '/');
            $baseName = pathinfo($filename, PATHINFO_FILENAME);

            foreach ($disk->files($variantDirectory) as $file) {
                if (str_starts_with(pathinfo($file, PATHINFO_FILENAME), "{$baseName}-")) {
                    $this->copyFile($file, "{$destination}/".basename($variantDirectory).'/'.basename($file));
                }
            }
        }

        return $targetPath;
    }

    private function copyFile(string $source, string $destination): void
    {
        $disk = Storage::disk('public');
        $stream = $disk->readStream($source);

        if ($stream === false) {
            throw new RuntimeException("Unable to read media: {$source}");
        }

        try {
            if (! $disk->writeStream($destination, $stream)) {
                throw new RuntimeException("Unable to copy media to {$destination}");
            }
        } finally {
            fclose($stream);
        }
    }
}
