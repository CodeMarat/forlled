<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class BecomePartnerPageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'form' => [
                'submit_button_text' => $this->submit_button_text,
                'fields' => [
                    'first_name' => $this->first_name_label,
                    'last_name' => $this->last_name_label,
                    'country' => $this->country_label,
                    'city' => $this->city_label,
                    'company' => $this->company_label,
                    'company_type' => $this->company_type_label,
                    'position' => $this->position_label,
                    'email' => $this->email_label,
                    'phone' => $this->phone_label,
                    'website' => $this->website_label,
                    'message' => $this->message_label,
                ],
                'options' => [
                    'country' => array_values($this->country_options ?? []),
                    'city' => array_values($this->city_options ?? []),
                    'company_type' => array_values($this->company_type_options ?? []),
                ],
            ],
        ];
    }
}
