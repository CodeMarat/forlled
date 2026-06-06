<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPage;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\View\PanelsRenderHook;

class ListBlogPosts extends ListRecords
{
    protected static string $resource = BlogPostResource::class;

    protected ?Alignment $headerActionsAlignment = Alignment::End;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $blogPageData = [];

    public ?BlogPage $blogPageRecord = null;

    public function mount(): void
    {
        parent::mount();

        $this->blogPageRecord = BlogPage::query()->firstOrCreate([]);
        $this->blogPageForm->fill($this->blogPageRecord->toArray());
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function blogPageForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Blog hero section')
                    ->description('Controls the editable promo block displayed above the list of blog posts.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('hero_image')
                                    ->label('Left image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('blog/hero')
                                    ->helperText('Recommended for the image block shown on the left side.'),
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('hero_badge')
                                            ->label('Badge text')
                                            ->maxLength(255)
                                            ->helperText('Example: NEW'),
                                        TextInput::make('hero_title')
                                            ->label('Heading')
                                            ->maxLength(255)
                                            ->helperText('Main title shown in the right content block.'),
                                        Textarea::make('hero_description')
                                            ->label('Description')
                                            ->rows(4)
                                            ->helperText('Short descriptive text under the heading.'),
                                        TextInput::make('hero_button_text')
                                            ->label('Button text')
                                            ->maxLength(255)
                                            ->helperText('Example: Read More'),
                                        TextInput::make('hero_button_url')
                                            ->label('Button URL')
                                            ->url()
                                            ->maxLength(255)
                                            ->helperText('Full link or relative path to open from the button.'),
                                        Actions::make([
                                            Action::make('saveBlogPage')
                                                ->label('Save hero section')
                                                ->submit('saveBlogPage'),
                                        ])
                                            ->alignment(Alignment::End),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('blogPageData')
            ->model($this->blogPageRecord);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getBlogPageFormContentComponent(),
                $this->getTabsContentComponent(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }

    public function saveBlogPage(): void
    {
        $data = $this->blogPageForm->getState();

        $this->blogPageRecord->fill($data);
        $this->blogPageRecord->save();

        Notification::make()
            ->title('Blog hero section saved')
            ->success()
            ->send();
    }

    protected function getBlogPageFormContentComponent(): Component
    {
        return Form::make([
            EmbeddedSchema::make('blogPageForm'),
        ])
            ->id('blog-page-form')
            ->livewireSubmitHandler('saveBlogPage');
    }
}
