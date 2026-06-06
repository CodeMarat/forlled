<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\LocationsPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_locations_index_returns_paginated_active_locations_payload(): void
    {
        Location::factory()->create([
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

        Location::factory()->create([
            'slug' => 'hidden-location',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/locations?per_page=50');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'ru-moscow-daiseiko')
            ->assertJsonPath('data.0.phones.0', '+7 (495) 225-94-57')
            ->assertJsonPath('meta.per_page', 50)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_locations_page_endpoint_returns_expected_page_payload(): void
    {
        LocationsPage::query()->create([
            'slug' => 'locations',
            'meta_title' => 'Location finder — FORLLE\'D',
            'meta_description' => 'Find regional offices and partners…',
            'hero_title' => 'Where to find us',
            'hero_description' => 'Step into a refined beauty experience…',
        ]);

        $response = $this->getJson('/api/v1/pages/locations');

        $response
            ->assertOk()
            ->assertJsonPath('slug', 'locations')
            ->assertJsonPath('meta_title', 'Location finder — FORLLE\'D')
            ->assertJsonPath('hero.title', 'Where to find us');
    }
}
