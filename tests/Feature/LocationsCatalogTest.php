<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\LocationsPage;
use App\Models\RepresentedCountry;
use App\Models\User;
use Database\Seeders\LocationsCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LocationsCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_import_is_idempotent_and_preserves_admin_edits(): void
    {
        $this->seed(LocationsCatalogSeeder::class);

        $this->assertSame(48, RepresentedCountry::count());
        $this->assertSame(54, Location::count());
        $this->assertSame(1, LocationsPage::count());

        $salon = Location::where('company', 'RESCUE SPA NYC')->firstOrFail();
        $salon->update(['company' => 'Renamed Rescue Spa', 'slug' => 'renamed-rescue-spa', 'website' => 'https://edited.example']);
        $country = RepresentedCountry::where('name', 'Armenia')->firstOrFail();
        $country->update(['name' => 'Edited Armenia', 'slug' => 'edited-armenia']);

        $this->seed(LocationsCatalogSeeder::class);

        $this->assertSame(48, RepresentedCountry::count());
        $this->assertSame(54, Location::count());
        $this->assertSame('https://edited.example', $salon->fresh()->website);
        $this->assertSame('Renamed Rescue Spa', $salon->fresh()->company);
        $this->assertSame('Edited Armenia', $country->fresh()->name);
        $this->assertFalse(Schema::hasColumn('locations', 'map_pin_x'));
        $this->assertFalse(Schema::hasColumn('locations', 'country_key'));
    }

    public function test_conversion_preserves_legacy_locations_in_separate_table(): void
    {
        $this->assertTrue(Schema::hasTable('legacy_locations_2026_11_09'));
        $this->assertTrue(Schema::hasColumn('legacy_locations_2026_11_09', 'country_key'));
        $this->assertFalse(Schema::hasColumn('locations', 'country_key'));
        $this->assertTrue(Schema::hasColumn('locations', 'state'));
        $this->assertSame(0, DB::table('locations')->count());
    }

    public function test_public_endpoints_keep_world_countries_separate_from_us_salons(): void
    {
        $this->seed(LocationsCatalogSeeder::class);

        $this->getJson('/api/v1/represented-countries')
            ->assertOk()
            ->assertJsonCount(48, 'data');

        $this->getJson('/api/v1/locations?search=RESCUE%20SPA&per_page=100')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/locations?state=NY&per_page=100')
            ->assertOk()
            ->assertJsonCount(21, 'data');

        $this->getJson('/api/v1/pages/locations')
            ->assertOk()
            ->assertJsonPath('us.hero.title', "Find a Forlle'd clinic near you");
    }

    public function test_region_endpoint_uses_visitor_ip_and_reports_uncertainty(): void
    {
        config()->set('services.ipinfo.token', 'test-token');
        Cache::clear();
        Http::fake(['api.ipinfo.io/*' => Http::response(['country_code' => 'US'])]);

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->getJson('/api/v1/visitor-region')
            ->assertOk()
            ->assertJsonPath('region', 'us')
            ->assertJsonPath('cache_ttl_seconds', 86400);

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->getJson('/api/v1/visitor-region')
            ->assertJsonPath('region', 'us');
        Http::assertSentCount(1);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->getJson('/api/v1/visitor-region')
            ->assertOk()
            ->assertJsonPath('region', null)
            ->assertJsonPath('cache_ttl_seconds', 0);
    }

    public function test_region_endpoint_ignores_untrusted_forwarded_ip(): void
    {
        config()->set('services.ipinfo.token', 'test-token');
        Http::fake(['api.ipinfo.io/*' => Http::response(['country_code' => 'AM'])]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_X_FORWARDED_FOR' => '1.1.1.1',
        ])->getJson('/api/v1/visitor-region')->assertJsonPath('region', 'world');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/lite/8.8.8.8'));
    }

    public function test_region_endpoint_uses_forwarded_ip_from_explicitly_trusted_proxy(): void
    {
        config()->set('services.ipinfo.token', 'test-token');
        Http::fake(['api.ipinfo.io/*' => Http::response(['country_code' => 'US'])]);
        TrustProxies::at(['127.0.0.1']);

        try {
            $this->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_X_FORWARDED_FOR' => '8.8.8.8',
            ])->getJson('/api/v1/visitor-region')->assertJsonPath('region', 'us');

            Http::assertSent(fn ($request) => str_contains($request->url(), '/lite/8.8.8.8'));
        } finally {
            TrustProxies::flushState();
        }
    }

    public function test_admin_has_separate_world_country_and_us_salon_forms(): void
    {
        config()->set('app.env', 'local');
        $this->actingAs(User::factory()->create());
        $this->seed(LocationsCatalogSeeder::class);

        $this->get('/admin/locations')
            ->assertOk()
            ->assertSee('Worldwide countries')
            ->assertSee('USA salons');

        $this->get('/admin/represented-countries')
            ->assertOk()
            ->assertSee('Worldwide countries')
            ->assertSee('USA salons');

        $this->get('/admin/locations/create')->assertOk()->assertSee('State code');
        $this->get('/admin/represented-countries/create')->assertOk()->assertSee('World map pin X');
        $this->get('/admin/locations/'.Location::firstOrFail()->id.'/edit')->assertOk()->assertSee('State code');
        $this->get('/admin/represented-countries/'.RepresentedCountry::firstOrFail()->id.'/edit')->assertOk()->assertSee('World map pin X');
    }

    public function test_geocoder_stores_place_id_without_persisting_google_coordinates(): void
    {
        $this->seed(LocationsCatalogSeeder::class);
        config()->set('services.google_maps.geocoding_key', 'test-key');
        Http::fake(['maps.googleapis.com/*' => Http::response([
            'status' => 'OK',
            'results' => [[
                'partial_match' => false,
                'place_id' => 'ChIJ_test_place',
                'geometry' => [
                    'location_type' => 'ROOFTOP',
                    'location' => ['lat' => 40.738, 'lng' => -73.99],
                ],
            ]],
        ])]);

        $this->artisan('locations:geocode-us-salons', ['--slug' => 'rescue-spa-nyc-new-york-ny'])->assertExitCode(0);

        $salon = Location::where('company', 'RESCUE SPA NYC')->firstOrFail();
        $this->assertSame('ChIJ_test_place', $salon->google_address_place_id);
        $this->assertNull($salon->latitude);
        $this->assertNull($salon->longitude);

        $this->artisan('locations:geocode-us-salons', ['--slug' => 'rescue-spa-nyc-new-york-ny'])->assertExitCode(0);
        Http::assertSentCount(1);
    }

    public function test_geocoder_skips_ambiguous_addresses_without_repeating_paid_lookups(): void
    {
        $this->seed(LocationsCatalogSeeder::class);
        config()->set('services.google_maps.geocoding_key', 'test-key');
        Http::fake(['maps.googleapis.com/*' => Http::response([
            'status' => 'OK',
            'results' => [[
                'partial_match' => true,
                'place_id' => 'ChIJ_ambiguous',
                'geometry' => ['location_type' => 'APPROXIMATE'],
            ]],
        ])]);

        $this->artisan('locations:geocode-us-salons', ['--slug' => 'rescue-spa-nyc-new-york-ny'])->assertExitCode(0);
        $salon = Location::where('company', 'RESCUE SPA NYC')->firstOrFail();
        $this->assertNull($salon->google_address_place_id);
        $this->assertNotNull($salon->place_lookup_attempted_at);

        $this->artisan('locations:geocode-us-salons', ['--slug' => 'rescue-spa-nyc-new-york-ny'])->assertExitCode(0);
        Http::assertSentCount(1);
    }
}
