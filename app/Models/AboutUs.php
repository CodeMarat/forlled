<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasAdminAudit;

    protected $fillable = [
        'hero_eyebrow',
        'hero_title',
        'hero_description',
        'hero_image',
        'hero_image_alt',
        'story_title',
        'story_description',
        'story_secondary_text',
        'story_image',
        'story_image_alt',
        'bottom_description',
        'bottom_secondary_text',
        'bottom_image',
        'bottom_image_alt',
    ];
}
