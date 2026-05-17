<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Pages;

use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\CategoryAttributeDefinitionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCategoryAttributeDefinition extends ViewRecord
{
    protected static string $resource = CategoryAttributeDefinitionResource::class;

    public function getTitle(): string
    {
        return __('View category attribute');
    }

    public function getBreadcrumb(): string
    {
        return __('View category attribute');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->translateLabel(),
        ];
    }
}
