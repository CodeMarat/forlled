<?php

namespace Tests\Feature;

use App\Filament\Resources\ContactUsRequestResource;
use App\Models\ContactUsRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class ContactUsRequestResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_us_requests_index_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(ContactUsRequestResource::getUrl('index'));

        $response->assertOk();
    }

    public function test_contact_us_request_edit_page_displays_existing_request_data(): void
    {
        $user = User::factory()->create();
        $contactUsRequest = ContactUsRequest::factory()->create([
            'name' => 'Jane Doe',
            'city' => 'Los Angeles',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(ContactUsRequestResource::getUrl('edit', ['record' => $contactUsRequest]));

        $response
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('Los Angeles');
    }
}
