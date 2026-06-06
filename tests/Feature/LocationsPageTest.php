<?php

namespace Tests\Feature;

use App\Filament\Pages\LocationsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_locations_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(LocationsPage::getUrl());

        $response->assertStatus(200);
    }
}
