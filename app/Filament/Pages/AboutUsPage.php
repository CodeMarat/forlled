<?php

namespace App\Filament\Pages;

use App\Models\AboutUs as AboutUsModel;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
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
use Filament\Schemas\Schema;

class AboutUsPage extends Page
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public ?AboutUsModel $record = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $navigationLabel = 'About Us';

    protected static ?int $navigationSort = -20;

    protected string $view = 'filament.pages.about-us-page';

    public function mount(): void
    {
        $this->record = AboutUsModel::query()->firstOrCreate([]);
        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hero section')
                    ->description('Top section with eyebrow, heading, text block, and the right-side product image.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('hero_eyebrow')
                                            ->label('Eyebrow text')
                                            ->maxLength(255)
                                            ->helperText('Example: OUR MISSION'),
                                        TextInput::make('hero_title')
                                            ->label('Heading')
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('Main title shown in the top-left text block.'),
                                        Textarea::make('hero_description')
                                            ->label('Description')
                                            ->rows(5)
                                            ->required()
                                            ->helperText('Main descriptive text for the hero section.'),
                                    ]),
                                FileUpload::make('hero_image')
                                    ->label('Hero image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('about-us')
                                    ->helperText('Top-right image block.'),
                            ]),
                    ]),
                Section::make('Story section')
                    ->description('Middle section with the left image and The Story content on the right.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('story_image')
                                    ->label('Story image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('about-us')
                                    ->helperText('Large image shown on the left side of the story block.'),
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('story_title')
                                            ->label('Section title')
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('Example: THE STORY'),
                                        Textarea::make('story_description')
                                            ->label('Main story text')
                                            ->rows(6)
                                            ->required(),
                                        Textarea::make('story_secondary_text')
                                            ->label('Secondary story text')
                                            ->rows(6),
                                    ]),
                            ]),
                    ]),
                Section::make('Bottom section')
                    ->description('Bottom text block with the right-side decorative image.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        Textarea::make('bottom_description')
                                            ->label('Primary text')
                                            ->rows(5)
                                            ->required()
                                            ->helperText('Top paragraph of the bottom-left text block.'),
                                        Textarea::make('bottom_secondary_text')
                                            ->label('Secondary text')
                                            ->rows(4)
                                            ->helperText('Additional paragraph shown below the primary text.'),
                                    ]),
                                FileUpload::make('bottom_image')
                                    ->label('Bottom image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('about-us')
                                    ->helperText('Bottom-right decorative image block.'),
                            ]),
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
            ->title('About Us page saved')
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
