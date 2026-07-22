<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Support\Facades\DB;
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

        $parent = EloquentCategory::query()->find($parentId);

        if ($parent === null) {
            throw ValidationException::withMessages([
                'parent_id' => 'Родительская категория не найдена.',
            ]);
        }

        if (
            $category->exists
            && is_string($category->path)
            && is_string($parent->path)
            && ($parent->path === $category->path || Str::startsWith($parent->path, $category->path . '/'))
        ) {
            throw ValidationException::withMessages([
                'parent_id' => 'Нельзя выбрать дочернюю категорию родителем текущей категории.',
            ]);
        }
    }

    public function sync(
        EloquentCategory $category,
        ?string $previousPath,
        int $previousDepth,
    ): void {
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

        if ($previousPath === null || $previousPath === $path) {
            return;
        }

        DB::update(
            <<<'SQL'
                UPDATE categories
                SET path = replace(path, ?, ?),
                    depth = depth + ?,
                    updated_at = ?
                WHERE path LIKE ?
                SQL,
            [
                $previousPath,
                $path,
                $depth - $previousDepth,
                now(),
                $previousPath . '/%',
            ],
        );
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
