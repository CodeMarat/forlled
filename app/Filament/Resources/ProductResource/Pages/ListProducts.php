<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Support\Products\ProductType;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected ?Alignment $headerActionsAlignment = Alignment::End;

    #[Url]
    public string $type = 'product';

    public function mount(): void
    {
        $this->type = ProductType::resolve($this->type)->value;

        parent::mount();
    }

    public function getTitle(): string
    {
        return ProductType::resolve($this->type)->label();
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->where('type', ProductType::resolve($this->type)->value),
            );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('products')
                ->label('Products')
                ->color($this->type === ProductType::Product->value ? 'primary' : 'gray')
                ->url(ProductResource::getUrl('index', ['type' => ProductType::Product->value])),
            Actions\Action::make('treatments')
                ->label('Treatments')
                ->color($this->type === ProductType::Treatment->value ? 'primary' : 'gray')
                ->url(ProductResource::getUrl('index', ['type' => ProductType::Treatment->value])),
            Actions\CreateAction::make()
                ->url(ProductResource::getUrl('create', ['type' => $this->type])),
        ];
    }
}
