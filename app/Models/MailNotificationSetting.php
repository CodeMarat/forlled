<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\MailNotificationSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailNotificationSetting extends Model
{
    /** @use HasFactory<MailNotificationSettingFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'id',
        'partner_request_email',
        'contact_us_request_email',
    ];
}
