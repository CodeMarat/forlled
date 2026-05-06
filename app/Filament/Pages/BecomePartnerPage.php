<?php

namespace App\Filament\Pages;

use App\Models\BecomePartnerPage as BecomePartnerPageModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BecomePartnerPage extends Page
{
    protected string $view = 'filament.pages.become-partner-page';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-hand-raised';

    protected static ?string $navigationLabel = 'Become Partner Page';

    protected static ?int $navigationSort = -90;

    public ?BecomePartnerPageModel $record = null;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->record = BecomePartnerPageModel::query()->firstOrCreate([
            'id' => 1,
        ], [
            'title' => 'BECOME A PARTNER',
            'description' => 'Join our growing beauty network. We collaborate with salons, clinics, influencers, and retailers who share our passion for quality, innovation, and customer experience. Let\'s create something beautiful together.',
            'submit_button_text' => 'Send Form',
            'first_name_label' => 'First Name',
            'last_name_label' => 'Last Name',
            'country_label' => 'Country',
            'city_label' => 'City',
            'company_label' => 'Company',
            'company_type_label' => 'Company Type',
            'position_label' => 'Position',
            'email_label' => 'Email',
            'phone_label' => 'Phone Number',
            'website_label' => 'Website',
            'message_label' => 'Message',
            'country_options' => [
                ['value' => 'Armenia'],
                ['value' => 'United States'],
            ],
            'city_options' => [
                ['value' => 'Yerevan'],
                ['value' => 'Los Angeles'],
            ],
            'company_type_options' => [
                ['value' => 'Salon'],
                ['value' => 'Clinic'],
                ['value' => 'Retailer'],
                ['value' => 'Influencer'],
            ],
        ]);

        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hero content')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('submit_button_text')
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Form labels')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name_label')->required()->maxLength(255),
                                TextInput::make('last_name_label')->required()->maxLength(255),
                                TextInput::make('country_label')->required()->maxLength(255),
                                TextInput::make('city_label')->required()->maxLength(255),
                                TextInput::make('company_label')->required()->maxLength(255),
                                TextInput::make('company_type_label')->required()->maxLength(255),
                                TextInput::make('position_label')->required()->maxLength(255),
                                TextInput::make('email_label')->required()->maxLength(255),
                                TextInput::make('phone_label')->required()->maxLength(255),
                                TextInput::make('website_label')->required()->maxLength(255),
                            ]),
                        TextInput::make('message_label')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Section::make('Select options')
                    ->description('Editable options for country, city, and company type fields.')
                    ->schema([
                        Repeater::make('country_options')
                            ->label('Country options')
                            ->schema([
                                TextInput::make('value')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
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
                            ->collapsible()
                            ->addActionLabel('Add city option')
                            ->columnSpanFull(),
                        Repeater::make('company_type_options')
                            ->label('Company type options')
                            ->schema([
                                TextInput::make('value')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->addActionLabel('Add company type option')
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
            ->title('Become partner page saved')
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
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Save')
                        ->submit('save'),
                ])->key('form-actions'),
            ]);
    }
}
