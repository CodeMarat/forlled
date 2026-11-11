<?php

namespace App\Filament\Resources\RepresentedCountries\Pages;

use App\Filament\Resources\RepresentedCountries\RepresentedCountryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRepresentedCountry extends CreateRecord
{
    protected static string $resource = RepresentedCountryResource::class;
}
