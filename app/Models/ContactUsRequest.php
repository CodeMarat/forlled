<?php

namespace App\Models;

use Database\Factories\ContactUsRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsRequest extends Model
{
    /** @use HasFactory<ContactUsRequestFactory> */
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
