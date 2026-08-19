<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Support\Images\VideoUploadDispatcher;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Alignment;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public static string|Alignment $formActionsAlignment = Alignment::End;

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

    protected function afterCreate(): void
    {
        app(VideoUploadDispatcher::class)->dispatch($this->record, $this->record->getAttributes());
    }
}
