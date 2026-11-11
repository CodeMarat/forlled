<?php

namespace Tests\Unit;

use App\Support\Locations\VisitorRegionResolver;
use Illuminate\Http\Client\Factory;
use PHPUnit\Framework\TestCase;

class VisitorRegionResolverTest extends TestCase
{
    public function test_us_ip_uses_us_variant(): void
    {
        $http = new Factory;
        $http->fake(['api.ipinfo.io/*' => $http->response(['country_code' => 'US'])]);

        $this->assertSame('us', (new VisitorRegionResolver($http, 'token'))->resolve('8.8.8.8'));
    }

    public function test_other_country_uses_world_variant(): void
    {
        $http = new Factory;
        $http->fake(['api.ipinfo.io/*' => $http->response(['country_code' => 'AM'])]);

        $this->assertSame('world', (new VisitorRegionResolver($http, 'token'))->resolve('1.1.1.1'));
    }

    public function test_failed_lookup_does_not_return_world_as_a_known_region(): void
    {
        $http = new Factory;
        $http->fake(['api.ipinfo.io/*' => $http->response([], 500)]);

        $this->assertNull((new VisitorRegionResolver($http, 'token'))->resolve('8.8.8.8'));
    }

    public function test_private_or_invalid_ip_is_not_sent_to_provider(): void
    {
        $http = new Factory;
        $http->fake();
        $resolver = new VisitorRegionResolver($http, 'token');

        $this->assertNull($resolver->resolve('127.0.0.1'));
        $this->assertNull($resolver->resolve('not-an-ip'));
        $http->assertNothingSent();
    }
}
