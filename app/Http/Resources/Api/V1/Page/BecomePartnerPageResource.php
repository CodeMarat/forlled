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
                    'country' => $this->optionValues($this->country_options),
                    'city' => $this->optionValues($this->city_options),
                    'company_type' => $this->optionValues($this->company_type_options),
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>|string>|null  $options
     * @return array<int, string>
     */
    protected function optionValues(?array $options): array
    {
        if (! is_array($options)) {
            return [];
        }

        return array_values(array_filter(array_map(
            function (mixed $option): ?string {
                if (is_string($option) && filled($option)) {
                    return $option;
                }

                if (is_array($option) && filled($option['value'] ?? null)) {
                    return (string) $option['value'];
                }

                return null;
            },
            $options,
        )));
    }
}
