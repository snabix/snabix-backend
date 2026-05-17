<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\GetCategoryAttributes;

use App\Shared\Domain\DTO\Output;

class GetCategoryAttributesOutput extends Output
{
    /**
     * @param array<string, mixed>             $category
     * @param array<int, array<string, mixed>> $items
     */
    public function __construct(
        public readonly array $category,
        public readonly array $items,
    ) {}
}
