<?php

namespace App\Filament\Pages;

use App\Models\SocialMedia;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class SocialMediaPage extends Page
{
    protected string $view = 'filament.pages.social-media-page';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Social Media';

    protected static ?int $navigationSort = -15;

    public ?SocialMedia $record = null;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->record = SocialMedia::query()->firstOrCreate(['id' => 1]);
        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Social media links')
                    ->description('Manage the public links used across the website and footer.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('instagram_url')
                                    ->label('Instagram')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Full Instagram profile URL.'),
                                TextInput::make('facebook_url')
                                    ->label('Facebook')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Full Facebook page URL.'),
                                TextInput::make('youtube_url')
                                    ->label('YouTube')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Full YouTube channel URL.'),
                                TextInput::make('linkedin_url')
                                    ->label('LinkedIn')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Full LinkedIn company or profile URL.'),
                            ]),
                    ]),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFormContentComponent(),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Social media links saved')
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
