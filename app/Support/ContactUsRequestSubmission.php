<?php

namespace App\Support;

use App\Mail\ContactUsRequestReceived;
use App\Models\ContactUsRequest;
use App\Models\MailNotificationSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactUsRequestSubmission
{
    /** @param array<string, mixed> $attributes */
    public function submit(array $attributes): ContactUsRequest
    {
        $contactUsRequest = ContactUsRequest::query()->create($attributes);
        $recipient = MailNotificationSetting::query()->whereKey(1)->value('contact_us_request_email')
            ?: config('services.contact_us_requests.recipient');

        if (is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Mail::to($recipient)->queue(new ContactUsRequestReceived($contactUsRequest));
        } else {
            Log::warning('Contact Us request email was not queued: CONTACT_US_REQUEST_RECIPIENT and PARTNER_REQUEST_RECIPIENT are not configured.');
        }

        return $contactUsRequest;
    }
}
