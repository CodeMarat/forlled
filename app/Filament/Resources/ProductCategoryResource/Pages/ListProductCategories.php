<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\ProductCategoryResource;
use App\Support\Products\ProductType;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

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
        return ProductType::resolve($this->type)->label().' Categories';
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
                ->url(ProductCategoryResource::getUrl('index', ['type' => ProductType::Product->value])),
            Actions\Action::make('treatments')
                ->label('Treatments')
                ->color($this->type === ProductType::Treatment->value ? 'primary' : 'gray')
                ->url(ProductCategoryResource::getUrl('index', ['type' => ProductType::Treatment->value])),
            Actions\CreateAction::make()
                ->url(ProductCategoryResource::getUrl('create', ['type' => $this->type])),
        ];
    }
}
