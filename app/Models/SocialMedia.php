<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\SocialMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    /** @use HasFactory<SocialMediaFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'instagram_url',
        'facebook_url',
        'youtube_url',
        'linkedin_url',
    ];
}
