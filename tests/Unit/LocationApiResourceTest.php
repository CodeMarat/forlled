<?php

namespace Tests\Unit;

use App\Http\Resources\Api\V1\LocationResource;
use App\Http\Resources\Api\V1\Page\LocationsPageResource;
use App\Models\Location;
use App\Models\LocationsPage;
use Illuminate\Http\Request;
use Tests\TestCase;

class LocationApiResourceTest extends TestCase
{
    public function test_location_resource_matches_expected_contract(): void
    {
        $location = new Location([
            'id' => 1,
            'slug' => 'ru-moscow-daiseiko',
            'sort_order' => 1,
            'country' => 'Russia',
            'country_key' => 'russia',
            'city' => 'Moscow',
            'company' => 'OOO "Daiseiko"',
            'address' => 'Bolshaya Polyanka st. 50/1 build. 4',
            'phones' => ['+7 (495) 225-94-57', '+7 (495) 225-94-58'],
            'email' => 'info@forlled-russia.ru',
            'map_pin_x' => 56,
            'map_pin_y' => 30,
            'is_active' => true,
        ]);

        $payload = LocationResource::make($location)->resolve(new Request);

        $this->assertSame('ru-moscow-daiseiko', $payload['slug']);
        $this->assertSame(['+7 (495) 225-94-57', '+7 (495) 225-94-58'], $payload['phones']);
        $this->assertTrue($payload['is_active']);
    }

    public function test_locations_page_resource_matches_expected_contract(): void
    {
        $page = new LocationsPage([
            'slug' => 'locations',
            'meta_title' => 'Location finder — FORLLE\'D',
            'meta_description' => 'Find regional offices and partners…',
            'hero_title' => 'Where to find us',
            'hero_description' => 'Step into a refined beauty experience…',
        ]);

        $payload = LocationsPageResource::make($page)->resolve(new Request);

        $this->assertSame('locations', $payload['slug']);
        $this->assertSame('Location finder — FORLLE\'D', $payload['meta_title']);
        $this->assertSame('Where to find us', $payload['hero']['title']);
    }
}
