<?php

namespace Tests\Unit;

use App\Http\Resources\Api\V1\Page\SocialMediaPageResource;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiSocialMediaPageResourceTest extends TestCase
{
    public function test_social_media_page_resource_returns_all_links(): void
    {
        $socialMedia = new SocialMedia([
            'instagram_url' => 'https://instagram.com/forlled',
            'facebook_url' => 'https://facebook.com/forlled',
            'youtube_url' => 'https://youtube.com/@forlled',
            'linkedin_url' => 'https://linkedin.com/company/forlled',
        ]);

        $payload = SocialMediaPageResource::make($socialMedia)->resolve(Request::create('/'));

        $this->assertSame('https://instagram.com/forlled', $payload['instagram_url']);
        $this->assertSame('https://facebook.com/forlled', $payload['facebook_url']);
        $this->assertSame('https://youtube.com/@forlled', $payload['youtube_url']);
        $this->assertSame('https://linkedin.com/company/forlled', $payload['linkedin_url']);
    }
}
