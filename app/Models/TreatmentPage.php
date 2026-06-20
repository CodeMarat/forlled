<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\TreatmentPageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentPage extends Model
{
    /** @use HasFactory<TreatmentPageFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'slug',
        'meta_title',
        'meta_description',
        'hero_title',
        'hero_description',
        'hero_button_text',
        'hero_button_url',
        'hero_image',
        'hero_image_alt',
    ];
}
