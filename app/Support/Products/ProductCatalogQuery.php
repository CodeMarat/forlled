<?php

namespace App\Support\Products;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class ProductCatalogQuery
{
    /**
     * @template TQuery of Builder|HasMany
     * @param  TQuery  $query
     * @return TQuery
     */
    public static function forCatalog(Builder|HasMany $query, string $catalog): Builder|HasMany
    {
        $hasCatalogsColumn = Schema::hasColumn('products', 'catalogs');
        $hasLegacyTypeColumn = Schema::hasColumn('products', 'type');

        if ($hasCatalogsColumn && $hasLegacyTypeColumn) {
            return $query->where(function (Builder $query) use ($catalog): void {
                $query->whereJsonContains('catalogs', $catalog)
                    ->orWhere(function (Builder $query) use ($catalog): void {
                        $query->whereNull('catalogs')
                            ->whereIn('type', self::legacyTypeValues($catalog));
                    });
            });
        }

        if ($hasCatalogsColumn) {
            return $query->whereJsonContains('catalogs', $catalog);
        }

        if ($hasLegacyTypeColumn) {
            return $query->whereIn('type', self::legacyTypeValues($catalog));
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    protected static function legacyTypeValues(string $catalog): array
    {
        return match ($catalog) {
            ProductType::Product->value => [
                ProductType::Product->value,
                'both',
            ],
            ProductType::Treatment->value => [
                ProductType::Treatment->value,
                'both',
            ],
            default => [$catalog],
        };
    }
}
