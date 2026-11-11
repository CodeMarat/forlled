<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Illuminate\Database\Eloquent\Model;

class LocationsPage extends Model
{
    use HasAdminAudit;

    protected $fillable = [
        'slug',
        'meta_title',
        'meta_description',
        'hero_title',
        'hero_description',
        'us_hero_eyebrow',
        'us_hero_title',
        'us_hero_description',
        'us_hero_image',
        'us_locations_title',
        'us_locations_description',
    ];
}
