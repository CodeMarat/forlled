<?php

namespace App\Filament\Resources\RepresentedCountries;

use App\Filament\Resources\RepresentedCountries\Pages\CreateRepresentedCountry;
use App\Filament\Resources\RepresentedCountries\Pages\EditRepresentedCountry;
use App\Filament\Resources\RepresentedCountries\Pages\ListRepresentedCountries;
use App\Models\RepresentedCountry;
use App\Support\Slugs\SlugGenerator;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepresentedCountryResource extends Resource
{
    protected static ?string $model = RepresentedCountry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Worldwide countries';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?RepresentedCountry $record, ?string $state): void {
                        $set('slug', SlugGenerator::uniqueFromParts(RepresentedCountry::class, [$state], $record));
                    })
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->unique(ignoreRecord: true),
                Grid::make(2)->schema([
                    TextInput::make('map_pin_x')
                        ->label('World map pin X (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),
                    TextInput::make('map_pin_y')
                        ->label('World map pin Y (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),
                ]),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Toggle::make('is_active')
                    ->label('Visible on website')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                CheckboxColumn::make('is_active')->label('Visible'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
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

    public static function getPages(): array
    {
        return [
            'index' => ListRepresentedCountries::route('/'),
            'create' => CreateRepresentedCountry::route('/create'),
            'edit' => EditRepresentedCountry::route('/{record}/edit'),
        ];
    }
}
