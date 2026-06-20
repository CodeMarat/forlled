<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    use HasAdminAudit;

    protected $fillable = [
        'title',
        'description',
        'name_label',
        'email_label',
        'country_label',
        'city_label',
        'message_label',
        'submit_button_text',
        'success_message',
        'country_options',
        'city_options',
    ];

    protected function casts(): array
    {
        return [
            'country_options' => 'array',
            'city_options' => 'array',
        ];
    }
}
