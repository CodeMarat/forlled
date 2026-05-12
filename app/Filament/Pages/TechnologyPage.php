<?php

namespace App\Filament\Pages;

use App\Models\TechnologyPage as TechnologyPageModel;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class TechnologyPage extends Page
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public ?TechnologyPageModel $record = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Technology';

    protected static ?int $navigationSort = -60;

    protected string $view = 'filament.pages.technology-page';

    public function mount(): void
    {
        $this->record = TechnologyPageModel::query()->firstOrCreate([]);
        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Technology')
                    ->tabs([
                        Tab::make('Overview')
                            ->schema([
                                Section::make('Page intro')
                                    ->schema([
                                        TextInput::make('page_title')
                                            ->label('Page title')
                                            ->required()
                                            ->maxLength(255)
                                            ->default('TECHNOLOGY'),
                                        Textarea::make('page_intro')
                                            ->label('Intro text')
                                            ->rows(4)
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                                Section::make('Delivery system section')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                FileUpload::make('delivery_system_image')
                                                    ->label('Illustration image')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('technology/overview')
                                                    ->helperText('Main visual shown next to the delivery system content block.'),
                                                Grid::make(1)
                                                    ->schema([
                                                        TextInput::make('delivery_system_title')
                                                            ->label('Section title')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Textarea::make('delivery_system_description')
                                                            ->label('Main description')
                                                            ->rows(6)
                                                            ->required(),
                                                        Textarea::make('delivery_system_secondary_text')
                                                            ->label('Secondary description')
                                                            ->rows(4),
                                                    ]),
                                            ]),
                                    ]),
                                Section::make('Method section')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Grid::make(1)
                                                    ->schema([
                                                        TextInput::make('method_title')
                                                            ->label('Section title')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Textarea::make('method_description')
                                                            ->label('Section description')
                                                            ->rows(4)
                                                            ->required(),
                                                        Repeater::make('method_benefits')
                                                            ->label('Benefit items')
                                                            ->schema([
                                                                TextInput::make('title')
                                                                    ->label('Title')
                                                                    ->required()
                                                                    ->maxLength(255),
                                                                Textarea::make('description')
                                                                    ->label('Description')
                                                                    ->rows(3)
                                                                    ->required(),
                                                            ])
                                                            ->addActionLabel('Add benefit')
                                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                                            ->reorderableWithButtons()
                                                            ->cloneable()
                                                            ->collapsible()
                                                            ->collapsed()
                                                            ->columns(1)
                                                            ->defaultItems(0)
                                                            ->columnSpanFull(),
                                                    ]),
                                                FileUpload::make('method_image')
                                                    ->label('Method illustration')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('technology/overview')
                                                    ->helperText('Visual used on the right side of the method section.'),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Ingredients')
                            ->schema([
                                Section::make('Ingredient cards')
                                    ->schema([
                                        TextInput::make('ingredients_section_title')
                                            ->label('Section title')
                                            ->required()
                                            ->maxLength(255),
                                        Repeater::make('ingredient_cards')
                                            ->label('Cards')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('title')
                                                            ->label('Card title')
                                                            ->required()
                                                            ->maxLength(255),
                                                        TextInput::make('badge')
                                                            ->label('Badge / short label')
                                                            ->maxLength(255)
                                                            ->helperText('Example: Ha, EM, Pt, Ce, CON'),
                                                        Textarea::make('description')
                                                            ->label('Description')
                                                            ->rows(5)
                                                            ->required()
                                                            ->columnSpanFull(),
                                                        FileUpload::make('icon')
                                                            ->label('Icon image')
                                                            ->image()
                                                            ->disk('public')
                                                            ->directory('technology/ingredients')
                                                            ->helperText('Optional image if the frontend uses a custom icon instead of text badge.')
                                                            ->columnSpanFull(),
                                                    ]),
                                            ])
                                            ->addActionLabel('Add card')
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                            ->reorderableWithButtons()
                                            ->cloneable()
                                            ->collapsible()
                                            ->collapsed()
                                            ->columns(1)
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Evidence section')
                                    ->schema([
                                        TextInput::make('evidence_title')
                                            ->label('Section title')
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('evidence_text')
                                            ->label('Description')
                                            ->rows(4)
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Case studies')
                            ->schema([
                                Section::make('Clinical case studies')
                                    ->schema([
                                        TextInput::make('case_studies_title')
                                            ->label('Section title')
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('case_studies_description')
                                            ->label('Section description')
                                            ->rows(3)
                                            ->required()
                                            ->columnSpanFull(),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('before_label')
                                                    ->label('Before label')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->default('BEFORE'),
                                                TextInput::make('after_label')
                                                    ->label('After label')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->default('AFTER'),
                                            ]),
                                        Repeater::make('case_studies')
                                            ->label('Case studies slider')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        FileUpload::make('before_image')
                                                            ->label('Before image')
                                                            ->image()
                                                            ->disk('public')
                                                            ->directory('technology/case-studies')
                                                            ->required(),
                                                        FileUpload::make('after_image')
                                                            ->label('After image')
                                                            ->image()
                                                            ->disk('public')
                                                            ->directory('technology/case-studies')
                                                            ->required(),
                                                        TextInput::make('duration')
                                                            ->label('Duration')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->helperText('Example: 8 weeks')
                                                            ->columnSpanFull(),
                                                        Textarea::make('result_text')
                                                            ->label('Result description')
                                                            ->rows(3)
                                                            ->required()
                                                            ->columnSpanFull(),
                                                    ]),
                                            ])
                                            ->addActionLabel('Add case study')
                                            ->itemLabel(fn (array $state): ?string => filled($state['duration'] ?? null) ? 'Duration: '.$state['duration'] : null)
                                            ->reorderableWithButtons()
                                            ->cloneable()
                                            ->collapsible()
                                            ->collapsed()
                                            ->columns(1)
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ]),
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
            ->title('Technology page saved')
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
                ])->key('form-actions'),
            ]);
    }
}
