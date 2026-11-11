<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class RepresentedCountryResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'map_pin_x' => $this->map_pin_x,
            'map_pin_y' => $this->map_pin_y,
        ];
    }
}
