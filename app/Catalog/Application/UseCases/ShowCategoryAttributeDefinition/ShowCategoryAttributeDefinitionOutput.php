<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ShowCategoryAttributeDefinition;

use App\Shared\Domain\DTO\Output;

class ShowCategoryAttributeDefinitionOutput extends Output
{
    /**
     * @param array<string, mixed> $item
     */
    public function __construct(
        public readonly array $item,
    ) {}
}
