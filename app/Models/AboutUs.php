<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    protected $fillable = [
        'hero_eyebrow',
        'hero_title',
        'hero_description',
        'hero_image',
        'story_title',
        'story_description',
        'story_secondary_text',
        'story_image',
        'bottom_description',
        'bottom_secondary_text',
        'bottom_image',
    ];
}
