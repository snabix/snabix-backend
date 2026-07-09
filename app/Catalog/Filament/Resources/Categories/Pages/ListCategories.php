<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Pages;

use App\Catalog\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return __('Category catalog');
    }

    public function getTabs(): array
    {
        return [
            'all'      => Tab::make(__('All categories')),
            'root'     => Tab::make(__('Root categories only'))
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->whereNull('parent_id')),
            'children' => Tab::make(__('Child categories only'))
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->whereNotNull('parent_id')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New category')
                ->translateLabel()
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}
