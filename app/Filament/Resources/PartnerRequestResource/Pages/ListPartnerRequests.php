<?php

namespace App\Filament\Resources\PartnerRequestResource\Pages;

use App\Filament\Resources\PartnerRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;

class ListPartnerRequests extends ListRecords
{
    protected static string $resource = PartnerRequestResource::class;

    protected ?Alignment $headerActionsAlignment = Alignment::End;
}
