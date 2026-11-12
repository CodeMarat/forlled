<x-mail::message>
# New partner request

**Name:** {{ $partnerRequest->first_name }} {{ $partnerRequest->last_name }}  
**Email:** {{ $partnerRequest->email }}

@foreach (['country' => 'Country', 'city' => 'City', 'company' => 'Company', 'company_type' => 'Company type', 'position' => 'Position', 'phone' => 'Phone', 'website' => 'Website'] as $field => $label)
@if (filled($partnerRequest->{$field}))
**{{ $label }}:** {{ $partnerRequest->{$field} }}

@endif
@endforeach

@if (filled($partnerRequest->message))
**Message:**

{{ $partnerRequest->message }}
@endif

<x-mail::button :url="\App\Filament\Resources\PartnerRequestResource::getUrl('edit', ['record' => $partnerRequest])">
View request in admin
</x-mail::button>
</x-mail::message>
