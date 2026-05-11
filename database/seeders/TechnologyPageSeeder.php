<?php

namespace Database\Seeders;

use App\Models\TechnologyPage;
use Illuminate\Database\Seeder;

class TechnologyPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TechnologyPage::query()->updateOrCreate(
            ['id' => 1],
            [
                'page_title' => 'TECHNOLOGY',
                'page_intro' => 'Our revolutionary approach combines cutting-edge biotechnology with the finest natural ingredients to create skincare solutions that deliver unprecedented results. Every formula is backed by rigorous research and clinical evidence.',
                'delivery_system_title' => "THE FORLLE'D INNOVATIVE DELIVERY SYSTEM",
                'delivery_system_description' => 'We design and manufacture all of our hi-tech skin care products in Japan specifically for doctors, aestheticians and other passionate skin professionals who expect nothing short of the state-of-the-art technology, cutting edge ingredients and excellent results. We are proud to meet and exceed those expectations and always remain their first choice.',
                'delivery_system_secondary_text' => "Forlle'd is currently represented in leading clinics, salons and spas in more than 40 countries.",
                'delivery_system_image' => null,
                'method_title' => 'METHOD. LOW MOLECULAR PIONEER',
                'method_description' => 'Our unique low-molecular formulations ensure active ingredients penetrate deep into the skin layers, working at the cellular level to stimulate natural regeneration processes.',
                'method_image' => null,
                'method_benefits' => [
                    [
                        'title' => 'SAFETY',
                        'description' => 'The procedure with powerful antioxidant protection for fading, dull, stressed skin of urban dwellers.',
                    ],
                    [
                        'title' => 'EVIDENCE BASE',
                        'description' => 'The procedure with powerful antioxidant protection for fading, dull, stressed skin of urban dwellers.',
                    ],
                ],
                'ingredients_section_title' => 'METHOD. LOW MOLECULAR PIONEER',
                'ingredient_cards' => [
                    [
                        'title' => 'HYALURONIC ACID',
                        'badge' => 'Ha',
                        'description' => 'This is a treatment with a high antioxidant activity due to a combination of products based on precious platinum, different forms of hyaluronic acid, biostimulating high-tech compounds and herbal extracts widely used in traditional Japanese aesthetic practice.',
                        'icon' => null,
                    ],
                    [
                        'title' => 'COMPLEX OF IONIZED MINERALS',
                        'badge' => 'Ha',
                        'description' => 'The procedure with powerful antioxidant protection for fading, dull, stressed skin of urban dwellers.',
                        'icon' => null,
                    ],
                    [
                        'title' => 'EM',
                        'badge' => 'EM',
                        'description' => 'The procedure with powerful antioxidant protection for fading, dull, stressed skin of urban dwellers. This is a treatment with a high antioxidant activity due to a combination of products based on precious platinum, different forms of hyaluronic acid, biostimulating high-tech compounds and herbal extracts widely used in traditional Japanese aesthetic practice.',
                        'icon' => null,
                    ],
                    [
                        'title' => 'HYALURONIC ACID',
                        'badge' => 'Pt',
                        'description' => 'The procedure with powerful antioxidant protection for fading, dull, stressed skin of urban dwellers. This is a treatment with a high antioxidant activity due to a combination of products based on precious platinum.',
                        'icon' => null,
                    ],
                    [
                        'title' => 'COMPLEX OF IONIZED MINERALS',
                        'badge' => 'Ce',
                        'description' => 'This is a treatment with a high antioxidant activity due to a combination of products based on precious platinum, different forms of hyaluronic acid, biostimulating high-tech compounds and herbal extracts widely used in traditional Japanese aesthetic practice.',
                        'icon' => null,
                    ],
                    [
                        'title' => 'EM',
                        'badge' => 'CON',
                        'description' => 'The procedure with powerful antioxidant protection for fading, dull, stressed skin of urban dwellers.',
                        'icon' => null,
                    ],
                ],
                'evidence_title' => 'EVIDENCE BASE',
                'evidence_text' => 'Our commitment to scientific excellence is demonstrated through comprehensive clinical studies and documented case results. Each product undergoes rigorous testing to ensure measurable, visible improvements in skin health and appearance.',
                'case_studies_title' => 'CLINICAL CASE STUDIES',
                'case_studies_description' => 'All case studies conducted under professional supervision. Individual results may vary. Clinical testing performed in accordance with international standards for cosmetic efficacy evaluation.',
                'before_label' => 'BEFORE',
                'after_label' => 'AFTER',
                'case_studies' => [
                    [
                        'before_image' => null,
                        'after_image' => null,
                        'duration' => '8 weeks',
                        'result_text' => 'Visible reduction in fine lines, improves skin texture and radiance.',
                    ],
                ],
            ],
        );
    }
}
