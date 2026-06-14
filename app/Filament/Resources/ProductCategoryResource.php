<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCategoryResource\Pages\CreateProductCategory;
use App\Filament\Resources\ProductCategoryResource\Pages\EditProductCategory;
use App\Filament\Resources\ProductCategoryResource\Pages\ListProductCategories;
use App\Models\ProductCategory;
use App\Support\Slugs\SlugGenerator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static ?int $navigationSort = -80;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Product Categories';

    public static function shouldRegisterNavigation(): bool
    {
        return rescue(
            fn (): bool => DatabaseSchema::hasTable('product_categories'),
            false,
            report: false,
        );
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Category details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Category name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?ProductCategory $record, ?string $state): void {
                                        $set('slug', SlugGenerator::uniqueFromParts(ProductCategory::class, [$state], $record));
                                    })
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->alphaDash()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Generated automatically from the category name.'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('type_label')
                                    ->label('Type label')
                                    ->required()
                                    ->maxLength(255)
                                    ->default('TYPE')
                                    ->helperText('Small label shown above the category title in the hero area.'),
                                TextInput::make('hero_title')
                                    ->label('Hero title')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Large title shown on the category page hero.'),
                                TextInput::make('sort_order')
                                    ->label('Display order')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Lower numbers appear first in category navigation.'),
                            ]),
                        FileUpload::make('hero_image')
                            ->label('Hero image')
                            ->image()
                            ->disk('public')
                            ->directory('products/categories')
                            ->helperText('Background or hero image shown at the top of the category page.')
                            ->columnSpanFull(),
                        TextInput::make('hero_image_alt')
                            ->label('Hero image alt text')
                            ->maxLength(255)
                            ->helperText('Describe the image to improve SEO and accessibility.')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Visible on website')
                            ->default(true)
                            ->helperText('Turn this off to hide the category from public category navigation.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductCategories::route('/'),
            'create' => CreateProductCategory::route('/create'),
            'edit' => EditProductCategory::route('/{record}/edit'),
        ];
    }
}
