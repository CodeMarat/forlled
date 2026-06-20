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
    ];
}
