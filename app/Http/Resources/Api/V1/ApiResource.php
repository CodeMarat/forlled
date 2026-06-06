<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    /**
     * @return array{
     *     path: string,
     *     url: string,
     *     variants: array<string, array{path: string, url: string}>
     * }|null
     */
    protected function image(?string $path, ?string $preferredVariant = null): ?array
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return [
                'path' => $path,
                'url' => $path,
                'variants' => [],
            ];
        }

        $source = [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ];

        $variants = $this->imageVariants($path);
        $selected = ($preferredVariant !== null && isset($variants[$preferredVariant]))
            ? $variants[$preferredVariant]
            : $source;

        return [
            'path' => $selected['path'],
            'url' => $selected['url'],
            'variants' => $variants,
        ];
    }

    /**
     * @return array<string, array{path: string, url: string}>
     */
    protected function imageVariants(string $path): array
    {
        $directory = trim(pathinfo($path, PATHINFO_DIRNAME), './');
        $baseName = pathinfo($path, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $disk = Storage::disk('public');
        $variants = [];

        /** @var array<string, array<string, mixed>> $configuredVariants */
        $configuredVariants = config('image_pipeline.variants', []);

        foreach ($configuredVariants as $variantName => $variantConfig) {
            $variantExtension = ($variantConfig['format'] ?? 'source') === 'webp'
                ? 'webp'
                : $extension;

            $variantPath = trim(collect([
                $directory,
                trim((string) config('image_pipeline.variants_directory'), '/')."/{$baseName}-{$variantName}.{$variantExtension}",
            ])->filter()->implode('/'), '/');

            if (! $disk->exists($variantPath)) {
                continue;
            }

            $variants[$variantName] = [
                'path' => $variantPath,
                'url' => $disk->url($variantPath),
            ];
        }

        return $variants;
    }

    /**
     * @param  array<int, array<string, mixed>>|string|null  $items
     * @return array<int, array<string, mixed>>
     */
    protected function values(array|string|null $items, ?string $key = null): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(
            function (mixed $item) use ($key): ?array {
                if ($key === null) {
                    return is_array($item) ? $item : null;
                }

                if (! is_array($item) || blank($item[$key] ?? null)) {
                    return null;
                }

                return [$key => $item[$key]];
            },
            $items,
        )));
    }
}
