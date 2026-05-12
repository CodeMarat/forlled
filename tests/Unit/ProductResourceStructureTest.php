<?php

namespace Tests\Unit;

use App\Filament\Resources\ProductCategoryResource;
use App\Filament\Resources\ProductResource;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ProductResourceStructureTest extends TestCase
{
    public function test_product_resource_contains_database_guard_methods(): void
    {
        $hasTableMethod = new ReflectionMethod(ProductResource::class, 'hasTable');
        $hasColumnMethod = new ReflectionMethod(ProductResource::class, 'hasColumn');

        $this->assertSame('hasTable', $hasTableMethod->getName());
        $this->assertTrue($hasTableMethod->isProtected());
        $this->assertTrue($hasTableMethod->isStatic());

        $this->assertSame('hasColumn', $hasColumnMethod->getName());
        $this->assertTrue($hasColumnMethod->isProtected());
        $this->assertTrue($hasColumnMethod->isStatic());
    }

    public function test_product_category_resource_can_control_navigation_registration(): void
    {
        $shouldRegisterNavigationMethod = new ReflectionMethod(ProductCategoryResource::class, 'shouldRegisterNavigation');

        $this->assertSame('shouldRegisterNavigation', $shouldRegisterNavigationMethod->getName());
        $this->assertTrue($shouldRegisterNavigationMethod->isPublic());
        $this->assertTrue($shouldRegisterNavigationMethod->isStatic());
    }
}
