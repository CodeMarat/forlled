<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_categories_index_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(ProductCategoryResource::getUrl('index'));

        $response->assertStatus(200);
    }

    public function test_product_category_edit_page_displays_existing_category_data(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create([
            'name' => 'Cleansers',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(ProductCategoryResource::getUrl('edit', ['record' => $category]));

        $response
            ->assertStatus(200)
            ->assertSee('Cleansers');
    }
}
