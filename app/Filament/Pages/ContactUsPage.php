<?php

namespace App\Filament\Pages;

use App\Models\ContactUs as ContactUsModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class ContactUsPage extends Page
{
    protected string $view = 'filament.pages.contact-us-page';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Contact Us Page';

    protected static ?int $navigationSort = -30;

    public ?ContactUsModel $record = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->record = ContactUsModel::query()->firstOrCreate([], [
            'name_label' => 'Name',
            'email_label' => 'Email',
            'country_label' => 'Country',
            'city_label' => 'City',
            'message_label' => 'Message',
            'submit_button_text' => 'SEND',
            'success_message' => 'Your message has been successfully sent. Our team will contact you shortly.',
            'country_options' => [
                ['value' => 'United States'],
            ],
            'city_options' => [
                ['value' => 'Los Angeles'],
            ],
        ]);
        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page content')
                    ->schema([
                        TextInput::make('title')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Form labels and messages')
                    ->schema([
                        TextInput::make('name_label')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email_label')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('country_label')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city_label')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('message_label')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('submit_button_text')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('success_message')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Select options')
                    ->schema([
                        Repeater::make('country_options')
                            ->label('Country options')
                            ->schema([
                                TextInput::make('value')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add country option')
                            ->columnSpanFull(),
                        Repeater::make('city_options')
                            ->label('City options')
                            ->schema([
                                TextInput::make('value')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add city option')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Contact us page saved')
            ->success()
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    protected function getFormContentComponent(): Component
    {
        return Form::make([
            Section::make()
                ->schema([EmbeddedSchema::make('form')])
                ->footerActions([
                    Action::make('save')
                        ->label('Save')
                        ->submit('save'),
                ])
                ->footerActionsAlignment(Alignment::End),
        ])
            ->id('form')
            ->livewireSubmitHandler('save');
    }
}
