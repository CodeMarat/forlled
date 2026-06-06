<?php

namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Resources\LocationResource;
use App\Models\LocationsPage;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\View\PanelsRenderHook;

class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected ?Alignment $headerActionsAlignment = Alignment::End;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $locationsPageData = [];

    public ?LocationsPage $locationsPageRecord = null;

    public function mount(): void
    {
        parent::mount();

        $this->locationsPageRecord = LocationResource::getLocationsPageRecord();
        $this->locationsPageForm->fill($this->locationsPageRecord->toArray());
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function locationsPageForm(Schema $schema): Schema
    {
        return $schema
            ->components(LocationResource::locationsPageFormSchema())
            ->statePath('locationsPageData')
            ->model($this->locationsPageRecord);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getLocationsPageFormContentComponent(),
                $this->getTabsContentComponent(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }

    public function saveLocationsPage(): void
    {
        $data = $this->locationsPageForm->getState();

        $this->locationsPageRecord->fill($data);
        $this->locationsPageRecord->save();

        Notification::make()
            ->title('Locations page settings saved')
            ->success()
            ->send();
    }

    protected function getLocationsPageFormContentComponent(): Component
    {
        return Form::make([
            EmbeddedSchema::make('locationsPageForm'),
        ])
            ->id('locations-page-form')
            ->livewireSubmitHandler('saveLocationsPage');
    }
}
