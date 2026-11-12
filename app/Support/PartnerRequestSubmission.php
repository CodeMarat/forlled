<?php

namespace App\Support;

use App\Mail\PartnerRequestReceived;
use App\Models\MailNotificationSetting;
use App\Models\PartnerRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PartnerRequestSubmission
{
    /** @param array<string, mixed> $attributes */
    public function submit(array $attributes): PartnerRequest
    {
        $partnerRequest = PartnerRequest::query()->create($attributes);
        $recipient = MailNotificationSetting::query()->whereKey(1)->value('partner_request_email')
            ?: config('services.partner_requests.recipient');

        if (is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Mail::to($recipient)->queue(new PartnerRequestReceived($partnerRequest));
        } else {
            Log::warning('Partner request email was not queued: PARTNER_REQUEST_RECIPIENT is not configured.');
        }

        return $partnerRequest;
    }
}
