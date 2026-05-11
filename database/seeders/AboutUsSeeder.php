<?php

namespace Database\Seeders;

use App\Models\AboutUs;
use Illuminate\Database\Seeder;

class AboutUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AboutUs::query()->updateOrCreate(
            ['id' => 1],
            [
                'hero_eyebrow' => 'OUR MISSION',
                'hero_title' => 'ABOUT US',
                'hero_description' => 'Being committed to improving skin quality, we study skin concerns to create efficient, scientific solutions, develop professional skincare products to enhance skin immunity, deliver comprehensive solutions for skincare professionals and strive to achieve healthy, radiant skin that reflects beauty and well-being.',
                'hero_image' => null,
                'story_title' => 'THE STORY',
                'story_description' => "For more than 15 years we've been dedicated to studying skin concerns and bringing highly efficient skin care solutions primarily for the professional market. Based on the high-end Japanese technology and deep scientific approach we deliver a comprehensive and technologically innovative product range for skin professionals and their first choice.",
                'story_secondary_text' => "At the core of the Forlle'd method lies the Forlle'd innovative Nano-Delivery System (FNDS) based on the patented technology by Dr. Makoto Hatto, which ensures an inimitable ability of Forlle'd products to deliver vital ingredients to the deepest layers of the skin in a non-invasive, atraumatic and safe way. This truly unique delivery system along with advanced production technology, patented ingredients, bioengineered formulations, published scientific research and clinical studies make Forlle'd outstanding in the skincare industry.",
                'story_image' => null,
                'bottom_description' => 'We design and manufacture all of our hi-tech skin care products in Japan specifically for doctors, aestheticians and other passionate skin professionals who expect nothing short of the state-of-the-art technology, cutting edge ingredients and excellent results. We are proud to meet and exceed those expectations and always remain their first choice.',
                'bottom_secondary_text' => "Forlle'd is currently represented in leading clinics, salons and spas in more than 40 countries.",
                'bottom_image' => null,
            ],
        );
    }
}
