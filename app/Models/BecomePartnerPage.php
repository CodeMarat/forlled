<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Illuminate\Database\Eloquent\Model;

class BecomePartnerPage extends Model
{
    use HasAdminAudit;

    protected $fillable = [
        'title',
        'description',
        'submit_button_text',
        'first_name_label',
        'last_name_label',
        'country_label',
        'city_label',
        'company_label',
        'company_type_label',
        'position_label',
        'email_label',
        'phone_label',
        'website_label',
        'message_label',
        'country_options',
        'city_options',
        'company_type_options',
    ];

    protected function casts(): array
    {
        return [
            'country_options' => 'array',
            'city_options' => 'array',
            'company_type_options' => 'array',
        ];
    }
}
