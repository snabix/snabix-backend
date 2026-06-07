<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryHierarchyService
{
    public function assertParentIsValid(EloquentCategory $category, ?string $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($category->exists && $category->id === $parentId) {
            throw ValidationException::withMessages([
                'parent_id' => 'Категория не может быть родителем самой себе.',
            ]);
        }

        $visited         = [];
        $currentParentId = $parentId;

        while ($currentParentId !== null) {
            if ($category->exists && $currentParentId === $category->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Нельзя выбрать дочернюю категорию родителем текущей категории.',
                ]);
            }

            if (in_array($currentParentId, $visited, true)) {
                break;
            }

            $visited[]       = $currentParentId;
            $parent          = EloquentCategory::query()->find($currentParentId);
            $currentParentId = $parent?->parent_id;
        }
    }

    public function sync(EloquentCategory $category): void
    {
        $category->refresh();

        $parent = $category->parentCategory()->first();
        $depth  = $parent !== null ? $parent->depth + 1 : 0;
        $path   = $parent !== null ? $parent->path . '/' . $category->slug : $category->slug;

        if ($category->depth !== $depth || $category->path !== $path) {
            $category->forceFill([
                'depth' => $depth,
                'path'  => $path,
            ])->saveQuietly();
        }

        EloquentCategory::query()
            ->where('parent_id', $category->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(function (EloquentCategory $child): void {
                $this->sync($child);
            });
    }

    public function indentedName(EloquentCategory $category): string
    {
        return str_repeat('— ', max($category->depth, 0)) . $category->name;
    }

    public function isAllowedParentOption(
        EloquentCategory $category,
        ?string $ignoreId,
        ?string $ignoredPath,
    ): bool {
        if ($ignoreId !== null && $category->id === $ignoreId) {
            return false;
        }

        if ($ignoredPath === null || $category->path === null) {
            return true;
        }

        return $category->path !== $ignoredPath
            && !Str::startsWith($category->path, $ignoredPath . '/');
    }
}
