<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\UpdateCategoryAttributeDefinition;

use App\Shared\Domain\DTO\Output;

class UpdateCategoryAttributeDefinitionOutput extends Output
{
    /**
     * @param array<string, mixed> $item
     */
    public function __construct(
        public readonly array $item,
    ) {}
}
