<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerRequest extends Model
{
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
