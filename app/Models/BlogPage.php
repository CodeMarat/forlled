<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPage extends Model
{
    protected $fillable = [
        'hero_badge',
        'hero_title',
        'hero_description',
        'hero_button_text',
        'hero_button_url',
        'hero_image',
        'hero_image_alt',
    ];
}
