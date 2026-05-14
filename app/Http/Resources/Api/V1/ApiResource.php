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
     * @return array{path: string, url: string}|null
     */
    protected function image(?string $path): ?array
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return [
                'path' => $path,
                'url' => $path,
            ];
        }

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ];
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
