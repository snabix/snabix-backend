<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ExportCategoryAttributeDefinitions;

use App\Shared\Domain\DTO\Output;

class ExportCategoryAttributeDefinitionsOutput extends Output
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<string, mixed>             $meta
     */
    public function __construct(
        public readonly array $items,
        public readonly array $meta,
    ) {}
}
