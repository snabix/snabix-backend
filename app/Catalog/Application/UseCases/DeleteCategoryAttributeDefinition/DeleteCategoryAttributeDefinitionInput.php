<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\DeleteCategoryAttributeDefinition;

use App\Shared\Domain\DTO\Input;

class DeleteCategoryAttributeDefinitionInput extends Input
{
    public function __construct(
        public readonly int $attributeDefinitionId,
    ) {}
}
