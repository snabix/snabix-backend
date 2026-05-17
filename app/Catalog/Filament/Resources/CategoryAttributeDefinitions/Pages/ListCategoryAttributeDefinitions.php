<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Pages;

use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\CategoryAttributeDefinitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoryAttributeDefinitions extends ListRecords
{
    protected static string $resource = CategoryAttributeDefinitionResource::class;

    public function getTitle(): string
    {
        return __('Category attributes');
    }

    public function getBreadcrumb(): ?string
    {
        return __('Category attributes');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->translateLabel(),
        ];
    }
}
