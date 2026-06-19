<?php

namespace Tests\Feature;

use App\Filament\Pages\FeaturedInPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class FeaturedInPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_in_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(FeaturedInPage::getUrl());

        $response->assertOk();
    }
}
