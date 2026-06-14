<?php

namespace App\Support\Slugs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SlugGenerator
{
    /**
     * @param  array<int, string|null>  $parts
     */
    public static function fromParts(array $parts): string
    {
        return Str::slug(
            Collection::make($parts)
                ->filter(fn (?string $part): bool => filled($part))
                ->implode(' '),
        );
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string|null>  $parts
     */
    public static function uniqueFromParts(string $modelClass, array $parts, ?Model $record = null, string $column = 'slug'): string
    {
        $baseSlug = static::fromParts($parts);

        if (blank($baseSlug)) {
            return '';
        }

        $query = $modelClass::query()
            ->where(function ($query) use ($baseSlug, $column): void {
                $query
                    ->where($column, $baseSlug)
                    ->orWhere($column, 'like', "{$baseSlug}-%");
            });

        if ($record?->exists) {
            $query->whereKeyNot($record->getKey());
        }

        /** @var Collection<int, string> $existingSlugs */
        $existingSlugs = $query
            ->pluck($column)
            ->filter(fn (mixed $slug): bool => is_string($slug))
            ->values();

        if (! $existingSlugs->contains($baseSlug)) {
            return $baseSlug;
        }

        $nextSuffix = $existingSlugs
            ->map(function (string $slug) use ($baseSlug): int {
                if ($slug === $baseSlug) {
                    return 0;
                }

                if (! str_starts_with($slug, "{$baseSlug}-")) {
                    return 0;
                }

                return (int) Str::after($slug, "{$baseSlug}-");
            })
            ->max() + 1;

        return "{$baseSlug}-{$nextSuffix}";
    }
}
