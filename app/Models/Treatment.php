<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\TreatmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    /** @use HasFactory<TreatmentFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
