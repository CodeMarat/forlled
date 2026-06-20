<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\ContactUsRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsRequest extends Model
{
    /** @use HasFactory<ContactUsRequestFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'country',
        'city',
        'message',
        'status',
        'admin_note',
    ];
}
