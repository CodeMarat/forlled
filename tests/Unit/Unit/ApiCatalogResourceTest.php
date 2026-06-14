<?php

namespace Tests\Unit\Unit;

use App\Http\Resources\Api\V1\BlogPostListResource;
use App\Http\Resources\Api\V1\BlogPostResource;
use App\Http\Resources\Api\V1\ProductCategoryResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\BlogPost;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ApiCatalogResourceTest extends TestCase
{
    public function test_blog_post_resources_split_listing_and_detail_payloads(): void
    {
        $post = new BlogPost([
            'title' => 'Beauty Signals',
            'slug' => 'beauty-signals',
            'excerpt' => 'Short excerpt',
            'content' => '<p>Full content</p>',
            'featured_image' => 'blog/posts/cover.jpg',
            'featured_image_alt' => 'Serum bottle on black background',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 3,
        ]);

        $listPayload = BlogPostListResource::make($post)->resolve(Request::create('/'));
        $detailPayload = BlogPostResource::make($post)->resolve(Request::create('/'));

        $this->assertArrayNotHasKey('content', $listPayload);
        $this->assertSame('<p>Full content</p>', $detailPayload['content']);
        $this->assertSame(url('/storage/blog/posts/cover.jpg'), $detailPayload['featured_image']['url']);
        $this->assertSame('Serum bottle on black background', $detailPayload['featured_image']['alt']);
    }

    public function test_product_category_resource_includes_navigation_and_products(): void
    {
        $category = new ProductCategory([
            'name' => 'Cleansers',
            'slug' => 'cleansers',
            'type_label' => 'TYPE',
            'hero_title' => 'Cleansers',
            'hero_image' => 'products/categories/cleansers.jpg',
            'hero_image_alt' => 'Cleanser category hero image',
            'sort_order' => 1,
        ]);

        $product = new Product([
            'name' => 'Gentle Cleanser',
            'slug' => 'gentle-cleanser',
            'size' => '150 ml',
            'listing_description' => 'Daily cleanser',
            'hero_image' => 'products/items/gentle-cleanser.jpg',
            'hero_image_alt' => 'Gentle cleanser product bottle',
            'is_favorite' => true,
        ]);

        $product->setRelation('productCategory', $category);
        $category->setRelation('products', new Collection([$product]));
        $category->setRelation('navigationCategories', new Collection([$category]));

        $payload = ProductCategoryResource::make($category)->resolve(Request::create('/'));

        $this->assertSame('Cleansers', $payload['navigation_categories'][0]['name']);
        $this->assertSame('gentle-cleanser', $payload['products'][0]['slug']);
        $this->assertTrue($payload['products'][0]['is_favorite']);
        $this->assertSame('Cleanser category hero image', $payload['hero_image']['alt']);
        $this->assertSame('Gentle cleanser product bottle', $payload['products'][0]['hero_image']['alt']);
    }

    public function test_product_resource_returns_detail_sections_and_recommendations(): void
    {
        $category = new ProductCategory([
            'name' => 'Serums',
            'slug' => 'serums',
            'type_label' => 'TYPE',
            'hero_title' => 'Serums',
            'hero_image' => 'products/categories/serums.jpg',
            'hero_image_alt' => 'Serums category hero image',
            'sort_order' => 2,
        ]);

        $recommendedProduct = new Product([
            'name' => 'Recovery Serum',
            'slug' => 'recovery-serum',
            'size' => '30 ml',
            'listing_description' => 'Recommendation',
            'hero_image' => 'products/items/recovery-serum.jpg',
            'hero_image_alt' => 'Recovery serum bottle',
            'is_favorite' => false,
        ]);
        $recommendedProduct->setRelation('productCategory', $category);

        $product = new Product([
            'name' => 'Hydra Serum',
            'slug' => 'hydra-serum',
            'size' => '30 ml',
            'listing_description' => 'Listing text',
            'description' => '<p>Description</p>',
            'hero_image' => 'products/items/hydra-serum.jpg',
            'hero_image_alt' => 'Hydra serum bottle',
            'side_image' => 'products/items/hydra-side.jpg',
            'side_image_alt' => 'Hydra serum lifestyle photo',
            'key_benefits' => [
                ['benefit' => 'Hydrates'],
            ],
            'detail_sections' => [
                ['title' => 'How to use', 'content' => '<p>Use daily</p>'],
            ],
            'recommendations_title' => 'Home routine',
            'combine_with_title' => 'Combine with a treatment',
            'combine_left_title' => 'Clinic',
            'combine_left_text' => '<p>Left</p>',
            'combine_right_title' => 'Home care',
            'combine_right_text' => '<p>Right</p>',
            'is_favorite' => true,
        ]);

        $product->setRelation('productCategory', $category);
        $product->setRelation('recommendedProducts', new Collection([$recommendedProduct]));
        $product->setRelation('navigationCategories', new Collection([$category]));

        $payload = ProductResource::make($product)->resolve(Request::create('/'));

        $this->assertSame('Hydrates', $payload['key_benefits'][0]['benefit']);
        $this->assertSame('How to use', $payload['sections'][0]['title']);
        $this->assertSame('recovery-serum', $payload['recommended_products'][0]['slug']);
        $this->assertSame(url('/storage/products/items/hydra-side.jpg'), $payload['side_image']['url']);
        $this->assertSame('Hydra serum lifestyle photo', $payload['side_image']['alt']);
        $this->assertSame('Recovery serum bottle', $payload['recommended_products'][0]['hero_image']['alt']);
    }
}
