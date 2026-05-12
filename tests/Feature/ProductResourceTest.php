<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(ProductResource::getUrl('index'));

        $response->assertStatus(200);
    }

    public function test_product_edit_page_displays_existing_product_data(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Creamy Wash',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(ProductResource::getUrl('edit', ['record' => $product]));

        $response
            ->assertStatus(200)
            ->assertSee('Creamy Wash');
    }
}
