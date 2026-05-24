<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Contracts;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function count(): int;

    /**
     * @return array<int, string>
     */
    public function parentOptions(?int $ignoreId = null): array;

    /**
     * @return Collection<int, EloquentCategory>
     */
    public function listRootCategories(bool $onlyActive = true): Collection;

    /**
     * @return Collection<int, EloquentCategory>
     */
    public function listBranch(int $categoryId, bool $onlyActive = true): Collection;

    /**
     * @param array<string, mixed> $attributes
     */
    public function save(array $attributes, ?int $id = null): EloquentCategory;

    public function findByParentAndName(?int $parentId, string $name): ?EloquentCategory;

    public function findById(int $id): ?EloquentCategory;
}
