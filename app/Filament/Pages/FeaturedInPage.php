<?php

namespace App\Filament\Pages;

use App\Models\FeaturedInPage as FeaturedInPageModel;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class FeaturedInPage extends Page
{
    protected string $view = 'filament.pages.featured-in-page';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Featured In';

    protected static ?int $navigationSort = -14;

    public ?FeaturedInPageModel $record = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->record = FeaturedInPageModel::query()->firstOrCreate([
            'id' => 1,
        ], [
            'title' => 'FEATURED IN',
            'logos' => [],
        ]);

        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Featured in section')
                    ->description('Manage the logos shown in the featured brands and media block.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Section title')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Example: FEATURED IN')
                            ->columnSpanFull(),
                        Repeater::make('logos')
                            ->label('Logos')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Logo image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('featured-in/logos')
                                    ->required(),
                                TextInput::make('alt')
                                    ->label('Logo alt text')
                                    ->maxLength(255)
                                    ->helperText('Describe the image to improve SEO and accessibility.')
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->grid(2)
                            ->addActionLabel('Add logo')
                            ->columnSpanFull(),
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
            ->title('Featured in page saved')
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
