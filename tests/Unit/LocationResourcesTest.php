<?php

namespace Tests\Unit;

use App\Http\Resources\Api\V1\LocationResource;
use App\Http\Resources\Api\V1\Page\LocationsPageResource;
use App\Http\Resources\Api\V1\RepresentedCountryResource;
use App\Models\Location;
use App\Models\LocationsPage;
use App\Models\RepresentedCountry;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class LocationResourcesTest extends TestCase
{
    public function test_us_salon_resource_contains_map_and_contact_fields(): void
    {
        $location = new Location([
            'company' => 'Rescue Spa NYC',
            'city' => 'New York',
            'state' => 'NY',
            'address' => '29 East 19th St',
            'website' => 'https://www.rescuespa.com/',
            'latitude' => '40.7380000',
            'longitude' => '-73.9900000',
        ]);

        $data = (new LocationResource($location))->toArray(Request::create('/'));

        $this->assertSame('NY', $data['state']);
        $this->assertSame('https://www.rescuespa.com/', $data['website']);
        $this->assertSame(40.738, $data['latitude']);
        $this->assertSame(-73.99, $data['longitude']);
    }

    public function test_world_country_resource_contains_map_pin(): void
    {
        $country = new RepresentedCountry(['name' => 'Armenia', 'slug' => 'armenia', 'map_pin_x' => 53]);

        $data = (new RepresentedCountryResource($country))->toArray(Request::create('/'));

        $this->assertSame('Armenia', $data['name']);
        $this->assertSame(53, $data['map_pin_x']);
    }

    public function test_page_resource_contains_both_page_variants(): void
    {
        $page = new LocationsPage([
            'hero_title' => 'Where to find us',
            'us_hero_title' => 'Find a clinic near you',
        ]);

        $data = (new LocationsPageResource($page))->toArray(Request::create('/'));

        $this->assertSame('Where to find us', $data['hero']['title']);
        $this->assertSame('Find a clinic near you', $data['us']['hero']['title']);
    }
}
