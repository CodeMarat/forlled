<?php

namespace Tests\Feature;

use App\Models\PartnerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_partner_request_with_valid_payload(): void
    {
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'country' => 'United States',
            'city' => 'New York',
            'company' => 'Acme Clinic',
            'company_type' => 'Clinic',
            'position' => 'Owner',
            'email' => 'john@example.com',
            'phone' => '+1 555 0100',
            'website' => 'https://example.com',
            'message' => 'Interested in partnership options.',
        ];

        $response = $this->postJson(route('partner-requests.store'), $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Partner request submitted successfully.');

        $this->assertDatabaseHas('partner_requests', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'status' => 'new',
        ]);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->postJson(route('partner-requests.store'), []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);

        $this->assertSame(0, PartnerRequest::query()->count());
    }
}
