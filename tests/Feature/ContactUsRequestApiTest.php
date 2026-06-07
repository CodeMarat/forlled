<?php

namespace Tests\Feature;

use App\Models\ContactUsRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class ContactUsRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_us_request_can_be_submitted(): void
    {
        $response = $this->postJson(route('api.v1.contact-us-requests.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'country' => 'United States',
            'city' => 'Los Angeles',
            'message' => 'Please contact me back.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Contact request submitted successfully.');

        $this->assertDatabaseHas(ContactUsRequest::class, [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'country' => 'United States',
            'city' => 'Los Angeles',
            'status' => 'new',
        ]);
    }

    public function test_contact_us_request_requires_name_and_email(): void
    {
        $response = $this->postJson(route('api.v1.contact-us-requests.store'), [
            'country' => 'United States',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'city', 'message']);
    }
}
