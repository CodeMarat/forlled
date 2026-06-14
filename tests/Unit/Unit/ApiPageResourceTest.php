<?php

namespace Tests\Unit\Unit;

use App\Http\Resources\Api\V1\Page\AboutUsPageResource;
use App\Http\Resources\Api\V1\Page\BecomePartnerPageResource;
use App\Http\Resources\Api\V1\Page\ContactUsPageResource;
use App\Http\Resources\Api\V1\Page\HomePageResource;
use App\Http\Resources\Api\V1\Page\TechnologyPageResource;
use App\Models\AboutUs;
use App\Models\BecomePartnerPage;
use App\Models\ContactUs;
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
            'hero_image_alt' => 'Glass bottle on reflective surface',
            'intro_text' => 'Intro',
            'favorites_title' => 'Favorites',
            'duo_left_image' => 'home/duo/left.jpg',
            'duo_left_image_alt' => 'Left product close-up',
            'duo_left_caption' => 'Left caption',
            'duo_right_image' => 'home/duo/right.jpg',
            'duo_right_image_alt' => 'Right product close-up',
            'duo_right_caption' => 'Right caption',
            'person_name' => 'Dr. Jane',
            'person_title' => 'Founder',
            'person_photo' => 'home/person/photo.jpg',
            'person_photo_alt' => 'Portrait of Dr. Jane',
            'person_text' => 'Person text',
            'newest_title' => 'Newest',
            'newest_description' => 'Newest description',
            'science_title' => 'Science',
            'science_text' => 'Science text',
            'science_button_text' => 'Read more',
            'science_button_url' => '/science',
            'gallery_image_1' => 'home/gallery/one.jpg',
            'gallery_image_1_alt' => 'Laboratory bottle',
            'gallery_image_2' => 'home/gallery/two.jpg',
            'gallery_image_2_alt' => 'Product texture close-up',
        ]);

        $payload = HomePageResource::make($homePage)->resolve(Request::create('/'));

        $this->assertSame('Glow', $payload['hero']['title']);
        $this->assertSame(url('/storage/home/hero/image.jpg'), $payload['hero']['image']['url']);
        $this->assertSame('Glass bottle on reflective surface', $payload['hero']['image']['alt']);
        $this->assertSame('Read more', $payload['science']['button']['text']);
        $this->assertCount(2, $payload['science']['gallery']);
        $this->assertSame(url('/storage/home/gallery/one.jpg'), $payload['science']['gallery'][0]['url']);
        $this->assertSame('Laboratory bottle', $payload['science']['gallery'][0]['alt']);
        $this->assertCount(2, $payload['gallery']);
    }

    public function test_about_us_resource_maps_all_content_blocks(): void
    {
        $aboutUs = new AboutUs([
            'hero_eyebrow' => 'OUR MISSION',
            'hero_title' => 'About Us',
            'hero_description' => 'Hero text',
            'hero_image' => 'about/hero.jpg',
            'hero_image_alt' => 'Woman holding product bottle',
            'story_title' => 'The Story',
            'story_description' => 'Story',
            'story_secondary_text' => 'More story',
            'story_image' => 'about/story.jpg',
            'story_image_alt' => 'Skincare product on white background',
            'bottom_description' => 'Bottom left',
            'bottom_secondary_text' => 'Bottom right',
            'bottom_image' => 'about/bottom.jpg',
            'bottom_image_alt' => 'Decorative botanical branch',
        ]);

        $payload = AboutUsPageResource::make($aboutUs)->resolve(Request::create('/'));

        $this->assertSame('OUR MISSION', $payload['hero']['eyebrow']);
        $this->assertSame(url('/storage/about/story.jpg'), $payload['story']['image']['url']);
        $this->assertSame('Skincare product on white background', $payload['story']['image']['alt']);
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
            'country_options' => [['value' => 'Armenia'], ['value' => 'UAE']],
            'city_options' => [['value' => 'Yerevan']],
            'company_type_options' => [['value' => 'Clinic']],
        ]);

        $payload = BecomePartnerPageResource::make($page)->resolve(Request::create('/'));

        $this->assertSame('Send', $payload['form']['submit_button_text']);
        $this->assertSame('Company type', $payload['form']['fields']['company_type']);
        $this->assertSame(['Clinic'], $payload['form']['options']['company_type']);
        $this->assertSame(['Armenia', 'UAE'], $payload['form']['options']['country']);
    }

    public function test_contact_us_resource_maps_form_labels_and_options(): void
    {
        $page = new ContactUs([
            'title' => 'Contact us',
            'description' => 'We will answer as soon as possible.',
            'name_label' => 'Name',
            'email_label' => 'Email',
            'country_label' => 'Country',
            'city_label' => 'City',
            'message_label' => 'Message',
            'submit_button_text' => 'SEND',
            'success_message' => 'Your message has been successfully sent.',
            'country_options' => [['value' => 'United States']],
            'city_options' => [['value' => 'Los Angeles']],
        ]);

        $payload = ContactUsPageResource::make($page)->resolve(Request::create('/'));

        $this->assertSame('Contact us', $payload['title']);
        $this->assertSame('SEND', $payload['form']['submit_button_text']);
        $this->assertSame('Name', $payload['form']['fields']['name']);
        $this->assertSame(['United States'], $payload['form']['options']['country']);
        $this->assertSame(['Los Angeles'], $payload['form']['options']['city']);
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
            'delivery_system_image_alt' => 'Illustrated delivery system diagram',
            'method_title' => 'Method',
            'method_description' => 'Method description',
            'method_image' => 'technology/method.jpg',
            'method_image_alt' => 'Skincare product tube on pedestal',
            'method_benefits' => [
                ['title' => 'Low molecular weight', 'description' => 'Improves delivery'],
            ],
            'ingredients_section_title' => 'Ingredients',
            'ingredient_cards' => [
                ['title' => 'Peptide', 'badge' => 'Pt', 'description' => 'Card text', 'icon' => 'technology/icon.jpg', 'icon_alt' => 'Peptide ingredient icon'],
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
                    'before_image_alt' => 'Before treatment skin result',
                    'after_image' => 'technology/after.jpg',
                    'after_image_alt' => 'After treatment skin result',
                    'duration' => '4 weeks',
                    'result_text' => 'Improvement',
                ],
            ],
        ]);

        $payload = TechnologyPageResource::make($page)->resolve(Request::create('/'));

        $this->assertSame('Low molecular weight', $payload['method']['benefits'][0]['title']);
        $this->assertSame('Improves delivery', $payload['method']['benefits'][0]['description']);
        $this->assertSame('Peptide', $payload['ingredients']['cards'][0]['title']);
        $this->assertSame('Pt', $payload['ingredients']['cards'][0]['badge']);
        $this->assertSame(url('/storage/technology/icon.jpg'), $payload['ingredients']['cards'][0]['icon']['url']);
        $this->assertSame('Peptide ingredient icon', $payload['ingredients']['cards'][0]['icon']['alt']);
        $this->assertSame(url('/storage/technology/after.jpg'), $payload['case_studies']['items'][0]['after_image']['url']);
        $this->assertSame('After treatment skin result', $payload['case_studies']['items'][0]['after_image']['alt']);
    }
}
