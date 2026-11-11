<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages\CreateLocation;
use App\Filament\Resources\LocationResource\Pages\EditLocation;
use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Models\Location;
use App\Models\LocationsPage;
use App\Support\Slugs\SlugGenerator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?int $navigationSort = -9;

    protected static ?string $navigationLabel = 'Locations';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Location details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->alphaDash()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Generated automatically from the state, city, and salon name.'),
                                TextInput::make('sort_order')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Lower numbers appear first.'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('state')
                                    ->label('State code')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?Location $record): void {
                                        $set('slug', SlugGenerator::uniqueFromParts(
                                            Location::class,
                                            [$get('state'), $get('city'), $get('company')],
                                            $record,
                                        ));
                                    })
                                    ->maxLength(2)
                                    ->helperText('Two-letter US state code, for example: NY.'),
                                TextInput::make('city')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?Location $record): void {
                                        $set('slug', SlugGenerator::uniqueFromParts(
                                            Location::class,
                                            [$get('state'), $get('city'), $get('company')],
                                            $record,
                                        ));
                                    })
                                    ->maxLength(255),
                                TextInput::make('company')
                                    ->label('Salon name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?Location $record): void {
                                        $set('slug', SlugGenerator::uniqueFromParts(
                                            Location::class,
                                            [$get('state'), $get('city'), $get('company')],
                                            $record,
                                        ));
                                    })
                                    ->maxLength(255),
                            ]),
                        TextInput::make('address')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('website')
                                    ->url()
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label('Visible on website')
                                    ->default(true),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->numeric()
                                    ->minValue(-90)
                                    ->maxValue(90)
                                    ->helperText('Required to show the salon as a Google Maps marker.'),
                                TextInput::make('longitude')
                                    ->numeric()
                                    ->minValue(-180)
                                    ->maxValue(180),
                            ]),
                        TextInput::make('google_address_place_id')
                            ->label('Google address Place ID')
                            ->maxLength(255)
                            ->helperText('Used to locate this salon on Google Maps when manual coordinates are empty.'),
                        Repeater::make('phones')
                            ->label('Phone numbers')
                            ->simple(
                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->required()
                                    ->tel()
                                    ->maxLength(255),
                            )
                            ->defaultItems(0)
                            ->addActionLabel('Add phone')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('state')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company')
                    ->label('Salon')
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
        return [];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()
            ->where('is_active', true)
            ->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLocations::route('/'),
            'create' => CreateLocation::route('/create'),
            'edit' => EditLocation::route('/{record}/edit'),
        ];
    }

    public static function locationsPageFormSchema(): array
    {
        return [
            Section::make('Page settings')
                ->description('Controls both public Locations page variants.')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta title')
                        ->maxLength(255)
                        ->helperText('Browser title and SEO title for the locations page.'),
                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->helperText('SEO description used for the locations page.'),
                    TextInput::make('hero_title')
                        ->label('Hero title')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('hero_description')
                        ->label('Hero description')
                        ->rows(4)
                        ->required(),
                    TextInput::make('us_hero_eyebrow')
                        ->label('USA hero eyebrow')
                        ->maxLength(255),
                    TextInput::make('us_hero_title')
                        ->label('USA hero title')
                        ->maxLength(255),
                    Textarea::make('us_hero_description')
                        ->label('USA hero description')
                        ->rows(4),
                    FileUpload::make('us_hero_image')
                        ->label('USA hero image')
                        ->image()
                        ->disk('public')
                        ->directory('pages/locations'),
                    TextInput::make('us_locations_title')
                        ->label('USA locations title')
                        ->maxLength(255),
                    Textarea::make('us_locations_description')
                        ->label('USA locations description')
                        ->rows(3),
                    Actions::make([
                        Action::make('saveLocationsPage')
                            ->label('Save')
                            ->submit('saveLocationsPage'),
                    ])
                        ->alignment(Alignment::End),
                ]),
        ];
    }

    public static function getLocationsPageRecord(): LocationsPage
    {
        return LocationsPage::query()->firstOrCreate([
            'slug' => 'locations',
        ]);
    }
}
