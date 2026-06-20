<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages\ListAuditLogs;
use App\Filament\Resources\AuditLogResource\Pages\ViewAuditLog;
use App\Models\AuditLog;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?int $navigationSort = 120;

    protected static ?string $navigationLabel = 'Activity Logs';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    public static function shouldRegisterNavigation(): bool
    {
        return rescue(
            fn (): bool => DatabaseSchema::hasTable('audit_logs'),
            false,
            report: false,
        );
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Activity details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Date')
                                    ->dateTime('M j, Y H:i'),
                                TextEntry::make('user.name')
                                    ->label('User')
                                    ->placeholder('System'),
                                TextEntry::make('page')
                                    ->label('Page'),
                                TextEntry::make('record_title')
                                    ->label('Record')
                                    ->placeholder('—'),
                                TextEntry::make('event')
                                    ->label('Event')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'created' => 'success',
                                        'updated' => 'warning',
                                        'deleted' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('request_url')
                                    ->label('Request URL')
                                    ->placeholder('—'),
                            ]),
                    ]),
                Section::make('Changed values')
                    ->schema([
                        TextEntry::make('changes_preview')
                            ->label('Change summary')
                            ->columnSpanFull(),
                        KeyValueEntry::make('old_values_display')
                            ->label('Old values')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),
                        KeyValueEntry::make('new_values_display')
                            ->label('New values')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('System'),
                TextColumn::make('page')
                    ->label('Page')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('record_title')
                    ->label('Record')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('event')
                    ->badge()
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                    ])
                    ->sortable(),
                TextColumn::make('changes_preview')
                    ->label('Changes')
                    ->limit(140)
                    ->tooltip(fn (AuditLog $record): string => $record->changes_preview)
                    ->wrap(),
                TextColumn::make('request_url')
                    ->label('Request')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name'),
                SelectFilter::make('page')
                    ->options(fn (): array => AuditLog::query()
                        ->orderBy('page')
                        ->pluck('page', 'page')
                        ->all()),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([])
            ->recordUrl(fn (AuditLog $record): string => static::getUrl('view', ['record' => $record]))
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
            'view' => ViewAuditLog::route('/{record}'),
        ];
    }
}
