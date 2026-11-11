<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'slug',
        'sort_order',
        'city',
        'state',
        'company',
        'address',
        'phones',
        'email',
        'website',
        'source_key',
        'latitude',
        'longitude',
        'google_address_place_id',
        'place_lookup_attempted_at',
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
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'place_lookup_attempted_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
