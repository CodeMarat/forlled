<?php

namespace App\Filament\Resources\AuditLogResource\Pages;

use App\Filament\Resources\AuditLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected ?Alignment $headerActionsAlignment = Alignment::End;
}
