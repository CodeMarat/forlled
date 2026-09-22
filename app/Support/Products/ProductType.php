<?php

namespace App\Support\Products;

enum ProductType: string
{
    case Product = 'product';
    case Treatment = 'treatment';

    public static function resolve(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return is_string($value) ? (self::tryFrom($value) ?? self::Product) : self::Product;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Product->value => 'Products',
            self::Treatment->value => 'Treatments',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Products',
            self::Treatment => 'Treatments',
        };
    }
}
