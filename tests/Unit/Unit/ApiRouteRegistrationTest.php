<?php

namespace Tests\Unit\Unit;

use Tests\TestCase;

class ApiRouteRegistrationTest extends TestCase
{
    public function test_page_routes_are_registered_under_v1_prefix(): void
    {
        $this->assertSame(url('/api/v1/pages/home'), route('api.v1.pages.home'));
        $this->assertSame(url('/api/v1/pages/about-us'), route('api.v1.pages.about-us'));
        $this->assertSame(url('/api/v1/pages/technology'), route('api.v1.pages.technology'));
        $this->assertSame(url('/api/v1/pages/contact-us'), route('api.v1.pages.contact-us'));
        $this->assertSame(url('/api/v1/pages/become-partner'), route('api.v1.pages.become-partner'));
        $this->assertSame(url('/api/v1/pages/blog'), route('api.v1.pages.blog'));
        $this->assertSame(url('/api/v1/pages/locations'), route('api.v1.pages.locations'));
        $this->assertSame(url('/api/v1/pages/social-media'), route('api.v1.pages.social-media'));
        $this->assertSame(url('/api/v1/pages/treatments'), route('api.v1.pages.treatments'));
    }

    public function test_catalog_and_partner_routes_are_registered(): void
    {
        $this->assertSame(url('/api/v1/blog-posts'), route('api.v1.blog-posts.index'));
        $this->assertSame(url('/api/v1/locations'), route('api.v1.locations.index'));
        $this->assertSame(
            url('/api/v1/blog-posts/example-post'),
            route('api.v1.blog-posts.show', ['slug' => 'example-post']),
        );
        $this->assertSame(url('/api/v1/contact-us-requests'), route('api.v1.contact-us-requests.store'));
        $this->assertSame(
            url('/api/v1/products/example-product'),
            route('api.v1.products.show', ['slug' => 'example-product']),
        );
        $this->assertSame(
            url('/api/v1/product-categories/example-category'),
            route('api.v1.product-categories.show', ['slug' => 'example-category']),
        );
        $this->assertSame(url('/api/v1/treatments'), route('api.v1.treatments.index'));
        $this->assertSame(url('/api/v1/partner-requests'), route('api.v1.partner-requests.store'));
    }
}
