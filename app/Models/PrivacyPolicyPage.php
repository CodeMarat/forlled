<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\PrivacyPolicyPageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyPolicyPage extends Model
{
    /** @use HasFactory<PrivacyPolicyPageFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'title',
        'content',
    ];
}
