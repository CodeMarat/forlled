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
            'country' => 'United States',
            'country_key' => 'usa',
            'city' => $this->city,
            'state' => $this->state,
            'company' => $this->company,
            'address' => $this->address,
            'phones' => array_values($this->phones ?? []),
            'email' => $this->email,
            'website' => $this->website,
            'latitude' => $this->latitude === null ? null : (float) $this->latitude,
            'longitude' => $this->longitude === null ? null : (float) $this->longitude,
            'google_address_place_id' => $this->google_address_place_id,
            'is_active' => $this->is_active,
        ];
    }
}
