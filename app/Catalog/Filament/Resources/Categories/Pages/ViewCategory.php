<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Pages;

use App\Catalog\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return __('View category');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->translateLabel(),
            DeleteAction::make()
                ->translateLabel(),
        ];
    }
}
