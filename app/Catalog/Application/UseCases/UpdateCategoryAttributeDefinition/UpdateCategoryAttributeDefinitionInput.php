<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\UpdateCategoryAttributeDefinition;

use App\Shared\Domain\DTO\Input;

class UpdateCategoryAttributeDefinitionInput extends Input
{
    /**
     * @param array<int, mixed>|null $options
     */
    public function __construct(
        public readonly int $attributeDefinitionId,
        public readonly int $categoryId,
        public readonly string $name,
        public readonly ?string $slug,
        public readonly int $type,
        public readonly ?string $unit,
        public readonly ?string $description,
        public readonly ?string $placeholder,
        public readonly ?string $helpText,
        public readonly ?array $defaultValue,
        public readonly ?string $groupName,
        public readonly ?array $options,
        public readonly bool $isRequired,
        public readonly bool $isFilterable,
        public readonly bool $showInCard,
        public readonly bool $isActive,
        public readonly bool $appliesToChildren,
        public readonly int $sortOrder,
    ) {}
}
