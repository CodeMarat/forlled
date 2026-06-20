<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Illuminate\Database\Eloquent\Model;

class FeaturedInPage extends Model
{
    use HasAdminAudit;

    protected $fillable = [
        'title',
        'logos',
    ];

    protected function casts(): array
    {
        return [
            'logos' => 'array',
        ];
    }
}
