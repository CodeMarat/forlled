<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactUsRequestResource\Pages\EditContactUsRequest;
use App\Filament\Resources\ContactUsRequestResource\Pages\ListContactUsRequests;
use App\Models\ContactUsRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactUsRequestResource extends Resource
{
    protected static ?string $model = ContactUsRequest::class;

    protected static ?int $navigationSort = -25;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Contact Us Requests';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Request details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->required()
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('country')
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->maxLength(255),
                            ]),
                        Textarea::make('message')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
                Section::make('Processing')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'in_progress' => 'In progress',
                                'resolved' => 'Resolved',
                                'spam' => 'Spam',
                            ])
                            ->required(),
                        Textarea::make('admin_note')
                            ->label('Admin note')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('country')
                    ->toggleable(),
                TextColumn::make('city')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'in_progress',
                        'success' => 'resolved',
                        'danger' => 'spam',
                    ])
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'in_progress' => 'In progress',
                        'resolved' => 'Resolved',
                        'spam' => 'Spam',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationBadge(): ?string
    {
        $newRequestsCount = static::getModel()::query()
            ->where('status', 'new')
            ->count();

        return $newRequestsCount > 0 ? (string) $newRequestsCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() !== null ? 'warning' : 'gray';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New contact requests';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactUsRequests::route('/'),
            'edit' => EditContactUsRequest::route('/{record}/edit'),
        ];
    }
}
