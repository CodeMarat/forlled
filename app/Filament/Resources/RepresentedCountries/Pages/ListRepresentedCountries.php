<?php

namespace App\Filament\Resources\RepresentedCountries\Pages;

use App\Filament\Resources\RepresentedCountries\RepresentedCountryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ListRepresentedCountries extends ListRecords
{
    protected static string $resource = RepresentedCountryResource::class;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.pages.locations-tabs'),
            EmbeddedTable::make(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
