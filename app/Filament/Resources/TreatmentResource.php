<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TreatmentResource\Pages\CreateTreatment;
use App\Filament\Resources\TreatmentResource\Pages\EditTreatment;
use App\Filament\Resources\TreatmentResource\Pages\ListTreatments;
use App\Models\Treatment;
use App\Models\TreatmentPage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TreatmentResource extends Resource
{
    protected static ?string $model = Treatment::class;

    protected static ?int $navigationSort = 100;

    protected static ?string $navigationLabel = 'Treatments';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('Treatment name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique public identifier used by the API.'),
                        TextInput::make('sort_order')
                            ->label('Order')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first.'),
                    ]),
                Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Visible on website')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Treatment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                CheckboxColumn::make('is_active')
                    ->label('Visible'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTreatments::route('/'),
            'create' => CreateTreatment::route('/create'),
            'edit' => EditTreatment::route('/{record}/edit'),
        ];
    }

    public static function treatmentsPageFormSchema(): array
    {
        return [
            Section::make('Page settings')
                ->description('Controls the content shown at the top of the public treatments page.')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta title')
                        ->maxLength(255)
                        ->helperText('Browser title and SEO title for the treatments page.'),
                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->helperText('SEO description used for the treatments page.'),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('hero_title')
                                ->label('Header')
                                ->required()
                                ->maxLength(255),
                            Grid::make(1)
                                ->schema([
                                    FileUpload::make('hero_image')
                                        ->label('Hero image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('treatments/hero')
                                        ->helperText('Large image displayed below the top content block.'),
                                    TextInput::make('hero_image_alt')
                                        ->label('Hero image alt text')
                                        ->maxLength(255)
                                        ->helperText('Describe the image to improve SEO and accessibility.'),
                                ]),
                        ]),
                    Textarea::make('hero_description')
                        ->label('Description')
                        ->required()
                        ->rows(4),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('hero_button_text')
                                ->label('Button text')
                                ->maxLength(255)
                                ->helperText('Example: BECOME A PARTNER'),
                            TextInput::make('hero_button_url')
                                ->label('Button URL')
                                ->url()
                                ->maxLength(255)
                                ->helperText('Full URL or relative path for the hero button.'),
                        ]),
                    Actions::make([
                        Action::make('saveTreatmentsPage')
                            ->label('Save')
                            ->submit('saveTreatmentsPage'),
                    ])
                        ->alignment(Alignment::End),
                ]),
        ];
    }

    public static function getTreatmentPageRecord(): TreatmentPage
    {
        return TreatmentPage::query()->firstOrCreate([
            'slug' => 'treatments',
        ]);
    }
}
