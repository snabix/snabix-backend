<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\CreateCategoryAttributeDefinition;

use App\Shared\Domain\DTO\Output;

class CreateCategoryAttributeDefinitionOutput extends Output
{
    /**
     * @param array<string, mixed> $item
     */
    public function __construct(
        public readonly array $item,
    ) {}
}
