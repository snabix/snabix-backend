<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ShowCategoryAttributeDefinition;

use App\Shared\Domain\DTO\Input;

class ShowCategoryAttributeDefinitionInput extends Input
{
    public function __construct(
        public readonly int $attributeDefinitionId,
    ) {}
}
