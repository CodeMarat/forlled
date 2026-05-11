<?php

namespace Tests\Feature;

use App\Filament\Pages\AboutUsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutUsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_us_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(AboutUsPage::getUrl());

        $response->assertStatus(200);
    }
}
