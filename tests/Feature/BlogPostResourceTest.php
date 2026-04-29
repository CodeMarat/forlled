<?php

namespace Tests\Feature;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_posts_index_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(BlogPostResource::getUrl('index'));

        $response->assertStatus(200);
    }

    public function test_blog_post_edit_page_displays_existing_post_data(): void
    {
        $user = User::factory()->create();
        $blogPost = BlogPost::factory()->create([
            'title' => 'Admin Blog Post',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(BlogPostResource::getUrl('edit', ['record' => $blogPost]));

        $response
            ->assertStatus(200)
            ->assertSee('Admin Blog Post');
    }
}
