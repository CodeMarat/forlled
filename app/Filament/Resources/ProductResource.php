<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Products\ProductType;
use App\Support\Slugs\SlugGenerator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $recordRouteKeyName = 'slug';

    protected static ?int $navigationSort = -90;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    public static function getFrontendProductUrl(Product $product): string
    {
        return is_string($product->slug) && $product->slug !== ''
            ? sprintf('/products/%s', $product->slug)
            : '/products';
    }

    public static function form(Schema $schema): Schema
    {
        $hasProductCategoriesTable = self::hasTable('product_categories');
        $hasProductCategoryPivot = self::hasTable('product_category_product');
        $hasProductRelatedTable = self::hasTable('product_related');

        return $schema
            ->schema([
                Tabs::make('Product')
                    ->tabs([
                        Tab::make('Overview')
                            ->schema([
                                Section::make('Product details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('category_type')
                                                    ->label('Type')
                                                    ->options(ProductType::options())
                                                    ->default(ProductType::Product->value)
                                                    ->dehydrated(false)
                                                    ->live()
                                                    ->afterStateHydrated(function (Select $component, ?Product $record): void {
                                                        $type = $record?->productCategories->first()?->type;

                                                        $component->state(
                                                            $type instanceof ProductType
                                                                ? $type->value
                                                                : ProductType::Product->value,
                                                        );
                                                    })
                                                    ->afterStateUpdated(function (Set $set): void {
                                                        $set('productCategories', []);
                                                    })
                                                    ->required()
                                                    ->native(false)
                                                    ->helperText('Choose which catalog this product belongs to.'),
                                                Select::make('productCategories')
                                                    ->label('Categories')
                                                    ->multiple()
                                                    ->relationship(
                                                        name: 'productCategories',
                                                        titleAttribute: 'name',
                                                        modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                                                            ->where('type', $get('category_type') ?: ProductType::Product->value),
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->required($hasProductCategoriesTable && $hasProductCategoryPivot)
                                                    ->visible($hasProductCategoriesTable && $hasProductCategoryPivot)
                                                    ->helperText('Only categories matching the selected type are shown.')
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label('Category name')
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                                                $set('slug', SlugGenerator::uniqueFromParts(ProductCategory::class, [$state]));
                                                            })
                                                            ->maxLength(255),
                                                        TextInput::make('slug')
                                                            ->label('Slug')
                                                            ->required()
                                                            ->disabled()
                                                            ->dehydrated()
                                                            ->alphaDash()
                                                            ->unique(ProductCategory::class, 'slug')
                                                            ->maxLength(255),
                                                        TextInput::make('type_label')
                                                            ->label('Type label')
                                                            ->required()
                                                            ->default('TYPE')
                                                            ->maxLength(255),
                                                        TagsInput::make('group_name')
                                                            ->label('Category group')
                                                            ->suggestions(fn (): array => ProductCategory::query()
                                                                ->whereNotNull('group_name')
                                                                ->where('group_name', '!=', '')
                                                                ->select('group_name')
                                                                ->distinct()
                                                                ->orderBy('group_name')
                                                                ->pluck('group_name')
                                                                ->all())
                                                            ->splitKeys(['Tab', 'Enter', ','])
                                                            ->separator(',')
                                                            ->afterStateHydrated(function (TagsInput $component, array|string|null $state): void {
                                                                if (is_array($state)) {
                                                                    $component->state(array_values(array_filter($state, fn (mixed $value): bool => filled($value))));

                                                                    return;
                                                                }

                                                                $component->state(filled($state) ? [trim($state)] : []);
                                                            })
                                                            ->afterStateUpdated(function (Set $set, array $state): void {
                                                                if (count($state) > 1) {
                                                                    $set('group_name', [trim((string) last($state))]);
                                                                }
                                                            })
                                                            ->dehydrateStateUsing(fn (array $state): ?string => filled($state[0] ?? null) ? trim((string) $state[0]) : null)
                                                            ->nestedRecursiveRules(['min:1', 'max:255'])
                                                            ->helperText('Optional. Leave empty if this category should not belong to a navigation group.'),
                                                        TextInput::make('hero_title')
                                                            ->label('Hero title')
                                                            ->required()
                                                            ->maxLength(255),
                                                    ])
                                                    ->createOptionUsing(function (array $data, Get $get): int {
                                                        $data['type'] = $get('category_type') ?: ProductType::Product->value;

                                                        return (int) ProductCategory::query()->create($data)->getKey();
                                                    }),
                                                TextInput::make('sort_order')
                                                    ->label('Display order')
                                                    ->required(self::hasColumn('products', 'sort_order'))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->helperText('Lower numbers appear first inside the category grid.')
                                                    ->visible(self::hasColumn('products', 'sort_order')),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Product name')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function (Set $set, ?Product $record, ?string $state): void {
                                                        $set('slug', SlugGenerator::uniqueFromParts(Product::class, [$state], $record));
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
                                                    ->helperText('Generated automatically from the product name.'),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('size')
                                                    ->label('Size')
                                                    ->maxLength(255)
                                                    ->helperText('Example: 150 ml / 5 fl oz'),
                                                Toggle::make('is_active')
                                                    ->label('Visible on website')
                                                    ->default(true)
                                                    ->visible(self::hasColumn('products', 'is_active')),
                                            ]),
                                        RichEditor::make('description')
                                            ->label('Main description')
                                            ->required()
                                            ->columnSpanFull()
                                            ->helperText('Main text shown in the top-right content block of the product detail page.'),
                                        RichEditor::make('listing_description')
                                            ->label('Category card description')
                                            ->columnSpanFull()
                                            ->helperText('Short text used in product cards and recommendations.'),
                                    ])
                                    ->columns(1),
                            ]),
                        Tab::make('Images')
                            ->schema([
                                Section::make('Product images')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Grid::make(1)
                                                    ->schema([
                                                        FileUpload::make('hero_image')
                                                            ->label('Main product image')
                                                            ->acceptedFileTypes(['image/*', 'video/*'])
                                                            ->disk('public')
                                                            ->directory('products/items')
                                                            ->helperText('Upload an image or video shown on product and category pages.'),
                                                    ]),
                                                Grid::make(1)
                                                    ->schema([
                                                        FileUpload::make('side_image')
                                                            ->label('Lifestyle image')
                                                            ->acceptedFileTypes(['image/*', 'video/*'])
                                                            ->disk('public')
                                                            ->directory('products/items')
                                                            ->helperText('Upload an image or video shown next to the accordion section.'),
                                                    ]),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),
                        Tab::make('Benefits')
                            ->schema([
                                Section::make('Key benefits')
                                    ->schema([
                                        Repeater::make('key_benefits')
                                            ->label('Benefits list')
                                            ->schema([
                                                TextInput::make('benefit')
                                                    ->label('Benefit')
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->defaultItems(0)
                                            ->addActionLabel('Add benefit')
                                            ->helperText('These are shown as bullet points in the product intro area.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ]),
                        Tab::make('Sections')
                            ->schema([
                                Section::make('Accordion sections')
                                    ->schema([
                                        Repeater::make('detail_sections')
                                            ->label('Detail sections')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('title')
                                                            ->label('Section title')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Toggle::make('is_visible')
                                                            ->label('Show section in product page')
                                                            ->default(true)
                                                            ->afterStateHydrated(function (Toggle $component, mixed $state): void {
                                                                $component->state($state === null ? true : (bool) $state);
                                                            })
                                                            ->helperText('Disable this to hide the block from the product page and API response.'),
                                                    ]),
                                                RichEditor::make('content')
                                                    ->label('Section content')
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->defaultItems(0)
                                            ->addActionLabel('Add section')
                                            ->helperText('Use this for Indications, Product density, Active ingredients, Before/after, How to use, and similar sections. You can disable any section without deleting it.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ]),
                        Tab::make('Recommendations')
                            ->visible($hasProductRelatedTable)
                            ->schema([
                                Section::make('Recommendations section')
                                    ->schema([
                                        TextInput::make('recommendations_title')
                                            ->label('Recommendations title')
                                            ->maxLength(255),
                                        Repeater::make('productRecommendations')
                                            ->label('Recommended products')
                                            ->relationship()
                                            ->orderColumn('sort_order')
                                            ->schema([
                                                Select::make('related_product_id')
                                                    ->label('Recommended product')
                                                    ->relationship(name: 'relatedProduct', titleAttribute: 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                    ->helperText('Choose another product to show in the recommendations block.'),
                                            ])
                                            ->defaultItems(0)
                                            ->addActionLabel('Add recommended product')
                                            ->helperText('This controls the cards shown in the home routine recommendations section.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ]),
                        Tab::make('Treatment')
                            ->schema([
                                Section::make('Combine with a treatment')
                                    ->schema([
                                        TextInput::make('combine_with_title')
                                            ->label('Section title')
                                            ->maxLength(255),
                                        Grid::make(2)
                                            ->schema([
                                                Section::make('Left block')
                                                    ->schema([
                                                        TextInput::make('combine_left_title')
                                                            ->label('Title')
                                                            ->maxLength(255),
                                                        RichEditor::make('combine_left_text')
                                                            ->label('Text'),
                                                    ]),
                                                Section::make('Right block')
                                                    ->schema([
                                                        TextInput::make('combine_right_title')
                                                            ->label('Title')
                                                            ->maxLength(255),
                                                        RichEditor::make('combine_right_text')
                                                            ->label('Text'),
                                                    ]),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),
                        Tab::make('Flags')
                            ->schema([
                                Section::make('Extra flags')
                                    ->schema([
                                        Toggle::make('is_favorite')
                                            ->label('All time favorite')
                                            ->default(false)
                                            ->helperText('Use this when the product should be highlighted in favorite or featured sections.')
                                            ->visible(self::hasColumn('products', 'is_favorite')),
                                    ])
                                    ->columns(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('name')
                ->label('Product')
                ->searchable()
                ->sortable(),
        ];

        if (self::hasTable('product_categories') && self::hasTable('product_category_product')) {
            $columns[] = TextColumn::make('productCategories.name')
                ->label('Categories')
                ->badge()
                ->searchable();
        }

        $columns[] = TextColumn::make('category_type')
            ->label('Type')
            ->badge()
            ->state(fn (Product $record): ?string => $record->productCategories->first()?->type?->label());

        if (self::hasColumn('products', 'size')) {
            $columns[] = TextColumn::make('size')
                ->label('Size')
                ->limit(20);
        }

        if (self::hasColumn('products', 'sort_order')) {
            $columns[] = TextColumn::make('sort_order')
                ->label('Order')
                ->sortable();
        }

        if (self::hasColumn('products', 'is_active')) {
            $columns[] = CheckboxColumn::make('is_active')
                ->label('Visible');
        }

        if (self::hasColumn('products', 'is_favorite')) {
            $columns[] = CheckboxColumn::make('is_favorite')
                ->label('Favorite');
        }

        $filters = [];

        if (self::hasTable('product_categories') && self::hasTable('product_category_product')) {
            $filters[] = SelectFilter::make('productCategories')
                ->label('Category')
                ->multiple()
                ->relationship('productCategories', 'name');
        }

        $filters[] = SelectFilter::make('category_type')
            ->label('Type')
            ->options(ProductType::options())
            ->query(function (Builder $query, array $data): Builder {
                $type = $data['value'] ?? null;

                if (! is_string($type) || blank($type)) {
                    return $query;
                }

                return $query->whereHas(
                    'productCategories',
                    fn (Builder $categoryQuery): Builder => $categoryQuery->where('type', $type),
                );
            });

        $table = $table
            ->columns($columns)
            ->filters($filters)
            ->actions([
                Action::make('viewProduct')
                    ->label('View product')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Product $record): string => self::getFrontendProductUrl($record), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

        if (self::hasColumn('products', 'sort_order')) {
            $table
                ->defaultSort('sort_order')
                ->reorderable('sort_order');
        }

        return $table;
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    protected static function hasTable(string $table): bool
    {
        return rescue(
            fn (): bool => DatabaseSchema::hasTable($table),
            false,
            report: false,
        );
    }

    protected static function hasColumn(string $table, string $column): bool
    {
        return rescue(
            fn (): bool => DatabaseSchema::hasColumn($table, $column),
            false,
            report: false,
        );
    }
}
