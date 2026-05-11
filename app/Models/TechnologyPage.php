<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnologyPage extends Model
{
    protected $fillable = [
        'page_title',
        'page_intro',
        'delivery_system_title',
        'delivery_system_description',
        'delivery_system_secondary_text',
        'delivery_system_image',
        'method_title',
        'method_description',
        'method_image',
        'method_benefits',
        'ingredients_section_title',
        'ingredient_cards',
        'evidence_title',
        'evidence_text',
        'case_studies_title',
        'case_studies_description',
        'before_label',
        'after_label',
        'case_studies',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method_benefits' => 'array',
            'ingredient_cards' => 'array',
            'case_studies' => 'array',
        ];
    }
}
