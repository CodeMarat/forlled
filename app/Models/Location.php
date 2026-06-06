<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'sort_order',
        'country',
        'country_key',
        'city',
        'company',
        'address',
        'phones',
        'email',
        'map_pin_x',
        'map_pin_y',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phones' => 'array',
            'sort_order' => 'integer',
            'map_pin_x' => 'integer',
            'map_pin_y' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
