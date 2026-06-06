<?php

namespace Tests\Feature;

use App\Filament\Resources\TreatmentResource;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_treatments_index_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(TreatmentResource::getUrl('index'));

        $response->assertStatus(200);
    }

    public function test_treatment_edit_page_displays_existing_treatment_data(): void
    {
        $user = User::factory()->create();
        $treatment = Treatment::factory()->create([
            'name' => 'SENSKI SKIN CREAM',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(TreatmentResource::getUrl('edit', ['record' => $treatment]));

        $response
            ->assertStatus(200)
            ->assertSee('SENSKI SKIN CREAM');
    }
}
