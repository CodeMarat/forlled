<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use App\Filament\Resources\BlogPostResource\Pages\ListBlogPosts;
use App\Models\BlogPost;
use App\Support\Slugs\SlugGenerator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?int $navigationSort = -70;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Blog Posts';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Post details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Post title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                                        $set('slug', SlugGenerator::fromParts([$state]));
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
                                    ->helperText('Generated automatically from the title.'),
                            ]),
                        Textarea::make('excerpt')
                            ->label('Short description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Optional summary for blog listings and previews.')
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('Post content')
                            ->columnSpanFull()
                            ->helperText('Main article body. This can be expanded further once the Figma content blocks are defined.'),
                    ]),
                Section::make('Publishing')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Display order')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Lower numbers are shown first when manual ordering is used.'),
                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'archived' => 'Archived',
                                    ])
                                    ->required()
                                    ->default('draft')
                                    ->helperText('Use draft until the content is ready to appear publicly.'),
                                DateTimePicker::make('published_at')
                                    ->label('Publish date')
                                    ->seconds(false)
                                    ->helperText('Optional. Set this when the post is scheduled or already published.'),
                            ]),
                        Grid::make(1)
                            ->schema([
                                FileUpload::make('featured_image')
                                    ->label('Featured image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('blog/posts')
                                    ->helperText('Optional cover image for listings and article headers.'),
                                TextInput::make('featured_image_alt')
                                    ->label('Featured image alt text')
                                    ->maxLength(255)
                                    ->helperText('Describe the image to improve SEO and accessibility.'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'published',
                        'warning' => 'archived',
                    ])
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Publish date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
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
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }
}
