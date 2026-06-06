<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class LocationResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'country' => $this->country,
            'country_key' => $this->country_key,
            'city' => $this->city,
            'company' => $this->company,
            'address' => $this->address,
            'phones' => array_values($this->phones ?? []),
            'email' => $this->email,
            'map_pin_x' => $this->map_pin_x,
            'map_pin_y' => $this->map_pin_y,
            'is_active' => $this->is_active,
        ];
    }
}
