<?php

namespace Tests\Feature;

use App\Filament\Resources\LocationResource;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_locations_index_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(LocationResource::getUrl('index'));

        $response->assertStatus(200);
    }

    public function test_location_edit_page_displays_existing_location_data(): void
    {
        $user = User::factory()->create();
        $location = Location::factory()->create([
            'city' => 'Moscow',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(LocationResource::getUrl('edit', ['record' => $location]));

        $response
            ->assertStatus(200)
            ->assertSee('Moscow');
    }
}
