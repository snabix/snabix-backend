<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repositories;

use App\Catalog\Application\Services\CategoryAttributeDefinitionNormalizer;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

readonly class EloquentCategoryAttributeDefinitionRepository implements CategoryAttributeDefinitionRepositoryInterface
{
    public function __construct(
        private CategoryAttributeDefinitionNormalizer $categoryAttributeDefinitionNormalizer,
    ) {}

    /**
     * @return Collection<int, EloquentCategoryAttributeDefinition>
     */
    public function list(bool $onlyActive = false): Collection
    {
        $query = EloquentCategoryAttributeDefinition::query()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, EloquentCategoryAttributeDefinition>
     */
    public function forCategory(int $categoryId, bool $onlyActive = true): Collection
    {
        $category         = EloquentCategory::query()->find($categoryId);

        if ($category === null) {
            return collect();
        }

        $directCategoryId = $category->id;
        $ancestorIds      = [];
        $currentParentId  = $category->parent_id;

        while ($currentParentId !== null) {
            $ancestorIds[]   = $currentParentId;
            $parentCategory  = EloquentCategory::query()->find($currentParentId);
            $currentParentId = $parentCategory?->parent_id;
        }

        $query            = EloquentCategoryAttributeDefinition::query()
            ->where(function ($builder) use ($directCategoryId, $ancestorIds): void {
                $builder->where('category_id', $directCategoryId);

                if ($ancestorIds !== []) {
                    $builder->orWhere(function ($nestedBuilder) use ($ancestorIds): void {
                        $nestedBuilder
                            ->whereIn('category_id', $ancestorIds)
                            ->where('applies_to_children', true);
                    });
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        /** @var Collection<int, EloquentCategoryAttributeDefinition> $definitions */
        $definitions      = $query->get();

        return $definitions->unique(fn(EloquentCategoryAttributeDefinition $definition): string => (string) $definition->id)->values();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function save(array $attributes, ?int $id = null): EloquentCategoryAttributeDefinition
    {
        $definition      = $id !== null
            ? EloquentCategoryAttributeDefinition::query()->findOrFail($id)
            : new EloquentCategoryAttributeDefinition();

        $definition->fill($this->categoryAttributeDefinitionNormalizer->normalize($attributes, $definition));
        $definition->save();

        return $definition->fresh('category') ?? $definition;
    }

    public function findById(int $id): ?EloquentCategoryAttributeDefinition
    {
        return EloquentCategoryAttributeDefinition::query()
            ->with('category')
            ->find($id);
    }

    public function findByCategoryAndSlug(int $categoryId, string $slug): ?EloquentCategoryAttributeDefinition
    {
        return EloquentCategoryAttributeDefinition::query()
            ->with('category')
            ->where('category_id', $categoryId)
            ->where('slug', $slug)
            ->first();
    }

    public function delete(EloquentCategoryAttributeDefinition $definition): void
    {
        if ($definition->listingValues()->exists()) {
            throw ValidationException::withMessages([
                'attributeDefinitionId' => ['Нельзя удалить характеристику, по которой уже есть значения в объявлениях. Отключите ее вместо удаления.'],
            ]);
        }

        $definition->delete();
    }
}
