<?php

namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Resources\LocationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Alignment;

class EditLocation extends EditRecord
{
    protected static string $resource = LocationResource::class;

    public static string|Alignment $formActionsAlignment = Alignment::End;

    protected ?Alignment $headerActionsAlignment = Alignment::End;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            DeleteAction::make(),
            $this->getCancelFormAction(),
            $this->getSaveFormAction(),
        ];
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([
            Section::make()
                ->schema([EmbeddedSchema::make('form')])
                ->footerActions($this->getFormActions())
                ->footerActionsAlignment(Alignment::End),
        ])
            ->id('form')
            ->livewireSubmitHandler($this->getSubmitFormLivewireMethodName());
    }
}
