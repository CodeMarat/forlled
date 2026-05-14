<?php

namespace Tests\Unit\Unit;

use App\Http\Resources\Api\V1\Page\AboutUsPageResource;
use App\Http\Resources\Api\V1\Page\BecomePartnerPageResource;
use App\Http\Resources\Api\V1\Page\HomePageResource;
use App\Http\Resources\Api\V1\Page\TechnologyPageResource;
use App\Models\AboutUs;
use App\Models\BecomePartnerPage;
use App\Models\HomePage;
use App\Models\TechnologyPage;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiPageResourceTest extends TestCase
{
    public function test_home_page_resource_returns_structured_sections(): void
    {
        $homePage = new HomePage([
            'hero_title' => 'Glow',
            'hero_subtitle' => 'Daily beauty',
            'hero_image' => 'home/hero/image.jpg',
            'intro_text' => 'Intro',
            'favorites_title' => 'Favorites',
            'duo_left_image' => 'home/duo/left.jpg',
            'duo_left_caption' => 'Left caption',
            'duo_right_image' => 'home/duo/right.jpg',
            'duo_right_caption' => 'Right caption',
            'person_name' => 'Dr. Jane',
            'person_title' => 'Founder',
            'person_photo' => 'home/person/photo.jpg',
            'person_text' => 'Person text',
            'newest_title' => 'Newest',
            'newest_description' => 'Newest description',
            'science_title' => 'Science',
            'science_text' => 'Science text',
            'science_button_text' => 'Read more',
            'science_button_url' => '/science',
            'gallery_image_1' => 'home/gallery/one.jpg',
            'gallery_image_2' => 'home/gallery/two.jpg',
        ]);

        $payload = HomePageResource::make($homePage)->resolve(Request::create('/'));

        $this->assertSame('Glow', $payload['hero']['title']);
        $this->assertSame('http://localhost/storage/home/hero/image.jpg', $payload['hero']['image']['url']);
        $this->assertSame('Read more', $payload['science']['button']['text']);
        $this->assertCount(2, $payload['gallery']);
    }

    public function test_about_us_resource_maps_all_content_blocks(): void
    {
        $aboutUs = new AboutUs([
            'hero_eyebrow' => 'OUR MISSION',
            'hero_title' => 'About Us',
            'hero_description' => 'Hero text',
            'hero_image' => 'about/hero.jpg',
            'story_title' => 'The Story',
            'story_description' => 'Story',
            'story_secondary_text' => 'More story',
            'story_image' => 'about/story.jpg',
            'bottom_description' => 'Bottom left',
            'bottom_secondary_text' => 'Bottom right',
            'bottom_image' => 'about/bottom.jpg',
        ]);

        $payload = AboutUsPageResource::make($aboutUs)->resolve(Request::create('/'));

        $this->assertSame('OUR MISSION', $payload['hero']['eyebrow']);
        $this->assertSame('http://localhost/storage/about/story.jpg', $payload['story']['image']['url']);
        $this->assertSame('Bottom right', $payload['bottom']['secondary_text']);
    }

    public function test_become_partner_resource_maps_form_labels_and_options(): void
    {
        $page = new BecomePartnerPage([
            'title' => 'Become partner',
            'description' => 'Description',
            'submit_button_text' => 'Send',
            'first_name_label' => 'First name',
            'last_name_label' => 'Last name',
            'country_label' => 'Country',
            'city_label' => 'City',
            'company_label' => 'Company',
            'company_type_label' => 'Company type',
            'position_label' => 'Position',
            'email_label' => 'Email',
            'phone_label' => 'Phone',
            'website_label' => 'Website',
            'message_label' => 'Message',
            'country_options' => ['Armenia', 'UAE'],
            'city_options' => ['Yerevan'],
            'company_type_options' => ['Clinic'],
        ]);

        $payload = BecomePartnerPageResource::make($page)->resolve(Request::create('/'));

        $this->assertSame('Send', $payload['form']['submit_button_text']);
        $this->assertSame('Company type', $payload['form']['fields']['company_type']);
        $this->assertSame(['Clinic'], $payload['form']['options']['company_type']);
    }

    public function test_technology_resource_maps_dynamic_collections(): void
    {
        $page = new TechnologyPage([
            'page_title' => 'Technology',
            'page_intro' => 'Intro',
            'delivery_system_title' => 'Delivery',
            'delivery_system_description' => 'Delivery description',
            'delivery_system_secondary_text' => 'Secondary',
            'delivery_system_image' => 'technology/delivery.jpg',
            'method_title' => 'Method',
            'method_description' => 'Method description',
            'method_image' => 'technology/method.jpg',
            'method_benefits' => [
                ['benefit' => 'Low molecular weight'],
            ],
            'ingredients_section_title' => 'Ingredients',
            'ingredient_cards' => [
                ['title' => 'Peptide', 'description' => 'Card text'],
            ],
            'evidence_title' => 'Evidence',
            'evidence_text' => 'Evidence text',
            'case_studies_title' => 'Case studies',
            'case_studies_description' => 'Description',
            'before_label' => 'Before',
            'after_label' => 'After',
            'case_studies' => [
                [
                    'before_image' => 'technology/before.jpg',
                    'after_image' => 'technology/after.jpg',
                    'duration' => '4 weeks',
                    'result_text' => 'Improvement',
                ],
            ],
        ]);

        $payload = TechnologyPageResource::make($page)->resolve(Request::create('/'));

        $this->assertSame('Low molecular weight', $payload['method']['benefits'][0]['benefit']);
        $this->assertSame('Peptide', $payload['ingredients']['cards'][0]['title']);
        $this->assertSame('http://localhost/storage/technology/after.jpg', $payload['case_studies']['items'][0]['after_image']['url']);
    }
}
