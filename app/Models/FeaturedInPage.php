<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedInPage extends Model
{
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
