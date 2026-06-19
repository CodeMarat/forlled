<?php

namespace Tests\Unit;

use App\Http\Resources\Api\V1\Page\FeaturedInPageResource;
use App\Models\FeaturedInPage;
use Illuminate\Http\Request;
use Tests\TestCase;

class FeaturedInPageResourceTest extends TestCase
{
    public function test_featured_in_page_resource_returns_expected_contract(): void
    {
        $page = new FeaturedInPage([
            'title' => 'FEATURED IN',
            'logos' => [
                ['image' => 'featured-in/logos/elle.png', 'alt' => 'Elle logo'],
            ],
        ]);

        $payload = FeaturedInPageResource::make($page)->resolve(Request::create('/'));

        $this->assertSame('FEATURED IN', $payload['title']);
        $this->assertCount(1, $payload['logos']);
        $this->assertSame(url('/storage/featured-in/logos/elle.png'), $payload['logos'][0]['url']);
        $this->assertSame('Elle logo', $payload['logos'][0]['alt']);
    }
}
