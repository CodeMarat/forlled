<x-mail::message>
# New contact request

**Name:** {{ $contactUsRequest->name }}  
**Email:** {{ $contactUsRequest->email }}  
**Country:** {{ $contactUsRequest->country }}  
**City:** {{ $contactUsRequest->city }}

**Message:**

{{ $contactUsRequest->message }}

<x-mail::button :url="\App\Filament\Resources\ContactUsRequestResource::getUrl('edit', ['record' => $contactUsRequest])">
View request in admin
</x-mail::button>
</x-mail::message>
