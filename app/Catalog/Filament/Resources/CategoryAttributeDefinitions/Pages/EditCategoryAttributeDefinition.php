<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Pages;

use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\CategoryAttributeDefinitionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoryAttributeDefinition extends EditRecord
{
    protected static string $resource = CategoryAttributeDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->translateLabel(),
            DeleteAction::make()
                ->translateLabel(),
        ];
    }
}
