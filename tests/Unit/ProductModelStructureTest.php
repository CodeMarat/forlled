<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ProductModelStructureTest extends TestCase
{
    public function test_product_model_contains_expected_fillable_attributes(): void
    {
        $product = new Product;

        $this->assertContains('product_category_id', $product->getFillable());
        $this->assertContains('slug', $product->getFillable());
        $this->assertContains('hero_image', $product->getFillable());
        $this->assertContains('detail_sections', $product->getFillable());
        $this->assertContains('combine_right_text', $product->getFillable());
    }

    public function test_product_model_relationship_methods_are_declared(): void
    {
        $productCategoryMethod = new ReflectionMethod(Product::class, 'productCategory');
        $productRecommendationsMethod = new ReflectionMethod(Product::class, 'productRecommendations');

        $this->assertSame('productCategory', $productCategoryMethod->getName());
        $this->assertSame('productRecommendations', $productRecommendationsMethod->getName());
    }

    public function test_product_category_relationship_method_is_declared(): void
    {
        $productsMethod = new ReflectionMethod(ProductCategory::class, 'products');

        $this->assertSame('products', $productsMethod->getName());
    }
}
