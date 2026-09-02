<?php

namespace App\Support\Products;

class ProductDetailSections
{
    /**
     * @param  array<int, mixed>  $sections
     * @return array<int, mixed>
     */
    public static function makeVisible(array $sections): array
    {
        return array_map(static function (mixed $section): mixed {
            if (! is_array($section)) {
                return $section;
            }

            $section['is_visible'] = true;

            return $section;
        }, $sections);
    }
}
