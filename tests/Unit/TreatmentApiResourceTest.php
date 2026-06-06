<?php

namespace Tests\Unit;

use App\Http\Resources\Api\V1\Page\TreatmentPageResource;
use App\Http\Resources\Api\V1\TreatmentResource;
use App\Models\Treatment;
use App\Models\TreatmentPage;
use Illuminate\Http\Request;
use Tests\TestCase;

class TreatmentApiResourceTest extends TestCase
{
    public function test_treatment_resource_matches_expected_contract(): void
    {
        $treatment = new Treatment([
            'id' => 1,
            'name' => 'SENSKI SKIN CREAM',
            'slug' => 'senski-skin-cream',
            'description' => 'Treatment description.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $payload = TreatmentResource::make($treatment)->resolve(new Request);

        $this->assertSame('SENSKI SKIN CREAM', $payload['name']);
        $this->assertSame('senski-skin-cream', $payload['slug']);
        $this->assertSame(1, $payload['sort_order']);
        $this->assertTrue($payload['is_active']);
    }

    public function test_treatment_page_resource_matches_expected_contract(): void
    {
        $page = new TreatmentPage([
            'slug' => 'treatments',
            'meta_title' => 'Treatments — FORLLE\'D',
            'meta_description' => 'Healthy skin starts with the right care.',
            'hero_title' => 'TREATMENTS',
            'hero_description' => 'Healthy skin starts with the right care.',
            'hero_button_text' => 'BECOME A PARTNER',
            'hero_button_url' => '/become-partner',
            'hero_image' => 'treatments/hero/cover.jpg',
        ]);

        $payload = TreatmentPageResource::make($page)->resolve(new Request);

        $this->assertSame('treatments', $payload['slug']);
        $this->assertSame('Treatments — FORLLE\'D', $payload['meta_title']);
        $this->assertSame('TREATMENTS', $payload['hero']['title']);
        $this->assertSame('BECOME A PARTNER', $payload['hero']['button']['text']);
    }
}
