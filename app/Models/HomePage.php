<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePage extends Model
{
    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'hero_image_alt',
        'intro_text',
        'favorites_title',
        'duo_left_image',
        'duo_left_image_alt',
        'duo_left_caption',
        'duo_right_image',
        'duo_right_image_alt',
        'duo_right_caption',
        'person_name',
        'person_title',
        'person_photo',
        'person_photo_alt',
        'person_text',
        'newest_title',
        'newest_description',
        'science_title',
        'science_text',
        'science_button_text',
        'science_button_url',
        'gallery_image_1',
        'gallery_image_1_alt',
        'gallery_image_2',
        'gallery_image_2_alt',
        'gallery_image_3',
        'gallery_image_3_alt',
        'gallery_image_4',
        'gallery_image_4_alt',
    ];
}
