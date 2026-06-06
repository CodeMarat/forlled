<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected ?Alignment $headerActionsAlignment = Alignment::End;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
