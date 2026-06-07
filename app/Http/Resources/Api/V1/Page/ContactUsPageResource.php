<?php

namespace App\Http\Resources\Api\V1\Page;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class ContactUsPageResource extends ApiResource
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
                'success_message' => $this->success_message,
                'fields' => [
                    'name' => $this->name_label,
                    'email' => $this->email_label,
                    'country' => $this->country_label,
                    'city' => $this->city_label,
                    'message' => $this->message_label,
                ],
                'options' => [
                    'country' => $this->optionValues($this->country_options),
                    'city' => $this->optionValues($this->city_options),
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
