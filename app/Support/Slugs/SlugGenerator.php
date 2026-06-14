<?php

namespace App\Support\Slugs;

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
}
