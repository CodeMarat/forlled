<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\RepresentedCountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepresentedCountry extends Model
{
    /** @use HasFactory<RepresentedCountryFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'source_key',
        'sort_order',
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
            'sort_order' => 'integer',
            'map_pin_x' => 'integer',
            'map_pin_y' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
