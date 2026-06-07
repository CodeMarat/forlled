<?php

namespace App\Filament\Resources\ContactUsRequestResource\Pages;

use App\Filament\Resources\ContactUsRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;

class ListContactUsRequests extends ListRecords
{
    protected static string $resource = ContactUsRequestResource::class;

    protected ?Alignment $headerActionsAlignment = Alignment::End;
}
