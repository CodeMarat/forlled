<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Alignment;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

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
}
