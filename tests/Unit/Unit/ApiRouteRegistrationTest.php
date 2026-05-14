<?php

namespace Tests\Unit\Unit;

use Tests\TestCase;

class ApiRouteRegistrationTest extends TestCase
{
    public function test_page_routes_are_registered_under_v1_prefix(): void
    {
        $this->assertSame('http://localhost/api/v1/pages/home', route('api.v1.pages.home'));
        $this->assertSame('http://localhost/api/v1/pages/about-us', route('api.v1.pages.about-us'));
        $this->assertSame('http://localhost/api/v1/pages/blog', route('api.v1.pages.blog'));
    }

    public function test_catalog_and_partner_routes_are_registered(): void
    {
        $this->assertSame('http://localhost/api/v1/blog-posts', route('api.v1.blog-posts.index'));
        $this->assertSame('http://localhost/api/v1/products/example-product', route('api.v1.products.show', 'example-product'));
        $this->assertSame('http://localhost/api/v1/product-categories/example-category', route('api.v1.product-categories.show', 'example-category'));
        $this->assertSame('http://localhost/api/v1/partner-requests', route('api.v1.partner-requests.store'));
    }
}
