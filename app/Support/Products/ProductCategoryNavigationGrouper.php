<?php

namespace App\Support\Products;

use App\Models\ProductCategory;
use Illuminate\Support\Collection;

class ProductCategoryNavigationGrouper
{
    /**
     * @param  Collection<int, ProductCategory>  $categories
     * @return Collection<int, array{name: string, categories: Collection<int, ProductCategory>}>
     */
    public function group(Collection $categories): Collection
    {
        return $categories
            ->groupBy(fn (ProductCategory $category): string => $this->groupName($category))
            ->map(fn (Collection $groupCategories, string $groupName): array => [
                'name' => $groupName,
                'categories' => $groupCategories->values(),
            ])
            ->values();
    }

    protected function groupName(ProductCategory $category): string
    {
        return filled($category->group_name)
            ? (string) $category->group_name
            : 'other';
    }
}
