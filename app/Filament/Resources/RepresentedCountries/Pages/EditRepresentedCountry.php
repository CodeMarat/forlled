<?php

namespace App\Filament\Resources\RepresentedCountries\Pages;

use App\Filament\Resources\RepresentedCountries\RepresentedCountryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepresentedCountry extends EditRecord
{
    protected static string $resource = RepresentedCountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
