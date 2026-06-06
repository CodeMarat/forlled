<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationsPage extends Model
{
    protected $fillable = [
        'slug',
        'meta_title',
        'meta_description',
        'hero_title',
        'hero_description',
    ];
}
