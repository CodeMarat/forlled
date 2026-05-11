<?php

namespace Tests\Feature;

use App\Filament\Pages\TechnologyPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnologyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_technology_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(TechnologyPage::getUrl());

        $response->assertStatus(200);
    }
}
