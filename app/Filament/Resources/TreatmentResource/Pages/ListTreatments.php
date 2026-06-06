<?php

namespace App\Filament\Resources\TreatmentResource\Pages;

use App\Filament\Resources\TreatmentResource;
use App\Models\TreatmentPage;
use Filament\Actions;
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

class ListTreatments extends ListRecords
{
    protected static string $resource = TreatmentResource::class;

    protected ?Alignment $headerActionsAlignment = Alignment::End;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $treatmentPageData = [];

    public ?TreatmentPage $treatmentPageRecord = null;

    public function mount(): void
    {
        parent::mount();

        $this->treatmentPageRecord = TreatmentResource::getTreatmentPageRecord();
        $this->treatmentsPageForm->fill($this->treatmentPageRecord->toArray());
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function treatmentsPageForm(Schema $schema): Schema
    {
        return $schema
            ->components(TreatmentResource::treatmentsPageFormSchema())
            ->statePath('treatmentPageData')
            ->model($this->treatmentPageRecord);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getTreatmentsPageFormContentComponent(),
                $this->getTabsContentComponent(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }

    public function saveTreatmentsPage(): void
    {
        $data = $this->treatmentsPageForm->getState();

        $this->treatmentPageRecord->fill($data);
        $this->treatmentPageRecord->save();

        Notification::make()
            ->title('Treatments page settings saved')
            ->success()
            ->send();
    }

    protected function getTreatmentsPageFormContentComponent(): Component
    {
        return Form::make([
            EmbeddedSchema::make('treatmentsPageForm'),
        ])
            ->id('treatments-page-form')
            ->livewireSubmitHandler('saveTreatmentsPage');
    }
}
