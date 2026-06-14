<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerRequestResource\Pages\EditPartnerRequest;
use App\Filament\Resources\PartnerRequestResource\Pages\ListPartnerRequests;
use App\Models\PartnerRequest;
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

class PartnerRequestResource extends Resource
{
    protected static ?string $model = PartnerRequest::class;

    protected static ?int $navigationSort = -40;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationLabel = 'Partner Requests';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Request details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')->required()->maxLength(255),
                                TextInput::make('last_name')->required()->maxLength(255),
                                TextInput::make('country')->maxLength(255),
                                TextInput::make('city')->maxLength(255),
                                TextInput::make('company')->maxLength(255),
                                TextInput::make('company_type')->maxLength(255),
                                TextInput::make('position')->maxLength(255),
                                TextInput::make('email')->required()->email()->maxLength(255),
                                TextInput::make('phone')->maxLength(255),
                                TextInput::make('website')->url()->maxLength(255),
                            ]),
                        Textarea::make('message')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('Processing')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'in_progress' => 'In progress',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
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
                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('company')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('company_type')
                    ->toggleable(),
                TextColumn::make('country')
                    ->toggleable(),
                TextColumn::make('city')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'in_progress',
                        'success' => 'approved',
                        'danger' => 'rejected',
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
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
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
        return [
            //
        ];
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
        return 'New partner requests';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartnerRequests::route('/'),
            'edit' => EditPartnerRequest::route('/{record}/edit'),
        ];
    }
}
