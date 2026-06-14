<?php

namespace App\Filament\Pages;

use App\Models\HomePage as HomePageModel;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class HomePage extends Page
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public ?HomePageModel $record = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Home page';

    protected static ?int $navigationSort = -100;

    protected string $view = 'filament.pages.home-page';

    public function mount(): void
    {
        $this->record = HomePageModel::query()->firstOrCreate([]);
        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Home page')
                    ->tabs([
                        Tab::make('Hero')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        FileUpload::make('hero_image')
                                            ->label('Hero image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('home/hero')
                                            ->columnSpanFull(),
                                        TextInput::make('hero_image_alt')
                                            ->label('Hero image alt text')
                                            ->maxLength(255)
                                            ->helperText('Describe the image to improve SEO and accessibility.')
                                            ->columnSpanFull(),
                                        Textarea::make('intro_text')
                                            ->rows(5)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Favorites')
                            ->schema([
                                TextInput::make('favorites_title')
                                    ->label('Section title')
                                    ->maxLength(255),
                            ]),
                        Tab::make('Images duo')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                FileUpload::make('duo_left_image')
                                                    ->label('Left image')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('home/duo'),
                                                TextInput::make('duo_left_image_alt')
                                                    ->label('Left image alt text')
                                                    ->maxLength(255)
                                                    ->helperText('Describe the image to improve SEO and accessibility.'),
                                                TextInput::make('duo_left_caption')
                                                    ->label('Left caption')
                                                    ->maxLength(255),
                                            ]),
                                        Grid::make(1)
                                            ->schema([
                                                FileUpload::make('duo_right_image')
                                                    ->label('Right image')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('home/duo'),
                                                TextInput::make('duo_right_image_alt')
                                                    ->label('Right image alt text')
                                                    ->maxLength(255)
                                                    ->helperText('Describe the image to improve SEO and accessibility.'),
                                                TextInput::make('duo_right_caption')
                                                    ->label('Right caption')
                                                    ->maxLength(255),
                                            ]),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Person')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                FileUpload::make('person_photo')
                                                    ->label('Photo')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('home/person'),
                                                TextInput::make('person_photo_alt')
                                                    ->label('Photo alt text')
                                                    ->maxLength(255)
                                                    ->helperText('Describe the image to improve SEO and accessibility.'),
                                            ]),
                                        Grid::make(1)
                                            ->schema([
                                                TextInput::make('person_name')
                                                    ->label('Name')
                                                    ->maxLength(255),
                                                TextInput::make('person_title')
                                                    ->label('Title')
                                                    ->maxLength(255),
                                            ]),
                                        Textarea::make('person_text')
                                            ->label('Quote / description')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Newest Editions')
                            ->schema([
                                TextInput::make('newest_title')
                                    ->label('Title')
                                    ->maxLength(255),
                                Textarea::make('newest_description')
                                    ->label('Description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Science section')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('science_title')
                                            ->label('Title')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Textarea::make('science_text')
                                            ->label('Description')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        TextInput::make('science_button_text')
                                            ->label('Button text')
                                            ->maxLength(255),
                                        TextInput::make('science_button_url')
                                            ->label('Button URL')
                                            ->url()
                                            ->maxLength(255),
                                        Grid::make(2)
                                            ->schema([
                                                Grid::make(1)
                                                    ->schema([
                                                        FileUpload::make('gallery_image_1')
                                                            ->label('Gallery image 1')
                                                            ->image()
                                                            ->disk('public')
                                                            ->directory('home/gallery'),
                                                        TextInput::make('gallery_image_1_alt')
                                                            ->label('Gallery image 1 alt text')
                                                            ->maxLength(255)
                                                            ->helperText('Describe the image to improve SEO and accessibility.'),
                                                    ]),
                                                Grid::make(1)
                                                    ->schema([
                                                        FileUpload::make('gallery_image_2')
                                                            ->label('Gallery image 2')
                                                            ->image()
                                                            ->disk('public')
                                                            ->directory('home/gallery'),
                                                        TextInput::make('gallery_image_2_alt')
                                                            ->label('Gallery image 2 alt text')
                                                            ->maxLength(255)
                                                            ->helperText('Describe the image to improve SEO and accessibility.'),
                                                    ]),
                                                Grid::make(1)
                                                    ->schema([
                                                        FileUpload::make('gallery_image_3')
                                                            ->label('Gallery image 3')
                                                            ->image()
                                                            ->disk('public')
                                                            ->directory('home/gallery'),
                                                        TextInput::make('gallery_image_3_alt')
                                                            ->label('Gallery image 3 alt text')
                                                            ->maxLength(255)
                                                            ->helperText('Describe the image to improve SEO and accessibility.'),
                                                    ]),
                                                Grid::make(1)
                                                    ->schema([
                                                        FileUpload::make('gallery_image_4')
                                                            ->label('Gallery image 4')
                                                            ->image()
                                                            ->disk('public')
                                                            ->directory('home/gallery'),
                                                        TextInput::make('gallery_image_4_alt')
                                                            ->label('Gallery image 4 alt text')
                                                            ->maxLength(255)
                                                            ->helperText('Describe the image to improve SEO and accessibility.'),
                                                    ]),
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
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
            ->title('Home page saved')
            ->success()
            ->send();
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
