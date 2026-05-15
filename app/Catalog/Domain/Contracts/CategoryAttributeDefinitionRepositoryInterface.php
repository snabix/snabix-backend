<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Contracts;

use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Support\Collection;

interface CategoryAttributeDefinitionRepositoryInterface
{
    /**
     * @return Collection<int, EloquentCategoryAttributeDefinition>
     */
    public function list(bool $onlyActive = false): Collection;

    /**
     * @return Collection<int, EloquentCategoryAttributeDefinition>
     */
    public function forCategory(int $categoryId, bool $onlyActive = true): Collection;

    /**
     * @param array<string, mixed> $attributes
     */
    public function save(array $attributes, ?int $id = null): EloquentCategoryAttributeDefinition;

    public function findById(int $id): ?EloquentCategoryAttributeDefinition;

    public function delete(EloquentCategoryAttributeDefinition $definition): void;
}
