<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Illuminate\Database\Eloquent\Model;

class PartnerRequest extends Model
{
    use HasAdminAudit;

    protected $fillable = [
        'first_name',
        'last_name',
        'country',
        'city',
        'company',
        'company_type',
        'position',
        'email',
        'phone',
        'website',
        'message',
        'status',
        'admin_note',
    ];
}
