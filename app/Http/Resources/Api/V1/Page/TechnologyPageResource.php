<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class TechnologyPageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'overview' => [
                'title' => $this->page_title,
                'intro' => $this->page_intro,
            ],
            'delivery_system' => [
                'title' => $this->delivery_system_title,
                'description' => $this->delivery_system_description,
                'secondary_text' => $this->delivery_system_secondary_text,
                'image' => $this->image($this->delivery_system_image),
            ],
            'method' => [
                'title' => $this->method_title,
                'description' => $this->method_description,
                'image' => $this->image($this->method_image),
                'benefits' => array_values(array_filter(array_map(
                    fn (mixed $item): ?array => is_array($item)
                        ? [
                            'title' => $item['title'] ?? null,
                            'description' => $item['description'] ?? null,
                        ]
                        : null,
                    is_array($this->method_benefits) ? $this->method_benefits : [],
                ))),
            ],
            'ingredients' => [
                'section_title' => $this->ingredients_section_title,
                'cards' => array_values(array_filter(array_map(
                    fn (mixed $item): ?array => is_array($item)
                        ? [
                            'title' => $item['title'] ?? null,
                            'badge' => $item['badge'] ?? null,
                            'description' => $item['description'] ?? null,
                            'icon' => $this->image($item['icon'] ?? null),
                        ]
                        : null,
                    is_array($this->ingredient_cards) ? $this->ingredient_cards : [],
                ))),
            ],
            'evidence' => [
                'title' => $this->evidence_title,
                'text' => $this->evidence_text,
            ],
            'case_studies' => [
                'title' => $this->case_studies_title,
                'description' => $this->case_studies_description,
                'before_label' => $this->before_label,
                'after_label' => $this->after_label,
                'items' => array_values(array_map(
                    fn (mixed $item): array => [
                        'before_image' => $this->image(is_array($item) ? $item['before_image'] ?? null : null),
                        'after_image' => $this->image(is_array($item) ? $item['after_image'] ?? null : null),
                        'duration' => is_array($item) ? ($item['duration'] ?? null) : null,
                        'result_text' => is_array($item) ? ($item['result_text'] ?? null) : null,
                    ],
                    is_array($this->case_studies) ? $this->case_studies : [],
                )),
            ],
        ];
    }
}
