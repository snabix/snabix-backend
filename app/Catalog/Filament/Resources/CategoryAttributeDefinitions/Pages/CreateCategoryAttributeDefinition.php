<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Pages;

use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\CategoryAttributeDefinitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryAttributeDefinition extends CreateRecord
{
    protected static string $resource = CategoryAttributeDefinitionResource::class;
}
