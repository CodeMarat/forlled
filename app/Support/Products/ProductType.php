<?php

namespace App\Support\Products;

enum ProductType: string
{
    case Product = 'product';
    case Treatment = 'treatment';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Product->value => 'Products',
            self::Treatment->value => 'Treatment',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Products',
            self::Treatment => 'Treatment',
        };
    }
}
