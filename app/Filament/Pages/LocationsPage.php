<?php

namespace App\Filament\Pages;

use App\Models\LocationsPage as LocationsPageModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class LocationsPage extends Page
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public ?LocationsPageModel $record = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Locations';

    protected static ?int $navigationSort = -10;

    protected string $view = 'filament.pages.locations-page';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $this->record = LocationsPageModel::query()->firstOrCreate([
            'slug' => 'locations',
        ]);

        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('SEO')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta title')
                            ->maxLength(255)
                            ->helperText('Browser title and SEO title for the locations page.'),
                        Textarea::make('meta_description')
                            ->label('Meta description')
                            ->rows(3)
                            ->helperText('SEO description used for the locations page.'),
                    ]),
                Section::make('Hero')
                    ->schema([
                        TextInput::make('hero_title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('hero_description')
                            ->label('Description')
                            ->rows(4)
                            ->required(),
                    ]),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Locations page saved')
            ->success()
            ->send();
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
                ])
                    ->alignment(Alignment::End)
                    ->key('form-actions'),
            ]);
    }
}
