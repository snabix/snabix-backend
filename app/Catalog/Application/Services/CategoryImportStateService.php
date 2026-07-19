<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryImportManifest;
use RuntimeException;

readonly class CategoryImportStateService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * @return array<string, EloquentCategory>
     */
    public function existingByExternalId(string $source): array
    {
        $categories = EloquentCategory::query()
            ->with('parentCategory')
            ->where('external_source', $source)
            ->get();
        $result     = [];

        foreach ($categories as $category) {
            if (! is_string($category->external_id) || $category->external_id === '') {
                throw new RuntimeException(sprintf('Imported category [%s] has no external ID.', $category->id));
            }

            $result[$category->external_id] = $category;
        }

        return $result;
    }

    public function find(string $source, string $externalId): ?EloquentCategory
    {
        return EloquentCategory::query()
            ->with('parentCategory')
            ->where('external_source', $source)
            ->where('external_id', $externalId)
            ->first();
    }

    public function required(string $source, string $externalId): EloquentCategory
    {
        $category = $this->find($source, $externalId);

        if ($category === null) {
            throw new RuntimeException(sprintf('Imported category [%s] does not exist.', $externalId));
        }

        return $category;
    }

    /**
     * @return array<string, mixed>
     */
    public function categoryState(EloquentCategory $category): array
    {
        $parent          = $category->parentCategory;
        $parentReference = null;

        if ($parent !== null) {
            $parentReference = $parent->external_source === $category->external_source
                && is_string($parent->external_id)
                ? 'external:' . $parent->external_id
                : 'category:' . $parent->id;
        }

        return [
            'categoryId'      => $category->id,
            'externalId'      => $category->external_id,
            'parentReference' => $parentReference,
            'name'            => $category->name,
            'slug'            => $category->slug,
            'sortOrder'       => $category->sort_order,
            'isActive'        => $category->is_active,
            'depth'           => $category->depth,
        ];
    }

    /**
     * @param array<string, mixed> $left
     * @param array<string, mixed> $right
     */
    public function statesMatch(array $left, array $right): bool
    {
        foreach (['externalId', 'parentReference', 'name', 'sortOrder', 'isActive'] as $field) {
            if (($left[$field] ?? null) !== ($right[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $state
     */
    public function persist(
        string $source,
        string $externalId,
        array $state,
    ): EloquentCategory {
        $category   = $this->find($source, $externalId);
        $name       = $state['name'] ?? null;
        $sortOrder  = $state['sortOrder'] ?? null;
        $isActive   = $state['isActive'] ?? null;

        if (! is_string($name) || ! is_int($sortOrder) || ! is_bool($isActive)) {
            throw new RuntimeException(sprintf('Manifest state for [%s] is invalid.', $externalId));
        }

        $attributes = [
            'parent_id'  => $this->resolveParentId($source, $state['parentReference'] ?? null),
            'name'       => $name,
            'sort_order' => $sortOrder,
            'is_active'  => $isActive,
        ];
        $slug       = $state['slug'] ?? null;

        if (is_string($slug) && $slug !== '') {
            $attributes['slug'] = $slug;
        }

        if ($category !== null) {
            $attributes['catalog_type'] = $category->catalog_type;
            $attributes['description']  = $category->description;
        }

        $saved      = $this->categoryRepository->save($attributes, $category?->id);

        $saved->forceFill([
            'external_source'     => $source,
            'external_id'         => $externalId,
            'source_last_seen_at' => now(),
        ])->save();

        return $saved;
    }

    public function markRecordsAsSeen(EloquentCategoryImportManifest $manifest): void
    {
        $externalIds = [];

        foreach ($manifest->records as $record) {
            $externalId = $record['externalId'] ?? null;

            if (is_string($externalId) && $externalId !== '') {
                $externalIds[] = $externalId;
            }
        }

        foreach (array_chunk($externalIds, 500) as $chunk) {
            EloquentCategory::query()
                ->where('external_source', $manifest->source)
                ->whereIn('external_id', $chunk)
                ->update([
                    'source_last_seen_at' => now(),
                ]);
        }
    }

    private function resolveParentId(string $source, mixed $reference): ?string
    {
        if ($reference === null) {
            return null;
        }

        if (! is_string($reference)) {
            throw new RuntimeException('Category import parent reference is invalid.');
        }

        if (str_starts_with($reference, 'external:')) {
            $externalId = substr($reference, strlen('external:'));

            return $this->required($source, $externalId)->id;
        }

        if (str_starts_with($reference, 'category:')) {
            $categoryId = substr($reference, strlen('category:'));

            if (! EloquentCategory::query()->whereKey($categoryId)->exists()) {
                throw new RuntimeException(sprintf('Rollback parent category [%s] no longer exists.', $categoryId));
            }

            return $categoryId;
        }

        throw new RuntimeException('Category import parent reference uses an unsupported format.');
    }
}
