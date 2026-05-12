<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Pages;

use App\Catalog\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return __('Category catalog');
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
