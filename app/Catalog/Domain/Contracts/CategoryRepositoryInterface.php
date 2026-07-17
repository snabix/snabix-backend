<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Contracts;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function count(): int;

    /**
     * @return array<string, string>
     */
    public function parentOptions(?string $ignoreId = null): array;

    /**
     * @return Collection<int, EloquentCategory>
     */
    public function listRootCategories(bool $onlyActive = true): Collection;

    /**
     * @return Collection<int, EloquentCategory>
     */
    public function listBranch(string $categoryId, bool $onlyActive = true): Collection;

    /**
     * @param array<string, mixed> $attributes
     */
    public function save(array $attributes, ?string $id = null): EloquentCategory;

    public function findById(string $id): ?EloquentCategory;
}
