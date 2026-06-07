<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Media\Infrastructure\Models\EloquentMedia;

readonly class CategoryIconMediaService
{
    public function replaceFromStoredUpload(EloquentCategory $category, string $sourceDisk, string $sourcePath): EloquentMedia
    {
        $media = $category
            ->addMediaFromDisk($sourcePath, $sourceDisk)
            ->usingName($category->name . ' icon')
            ->toMediaCollection('category_icons', 'public');

        $media->forceFill([
            'description' => 'Иконка категории ' . $category->name,
        ])->save();

        return EloquentMedia::query()
            ->whereKey($media->getKey())
            ->firstOrFail();
    }
}
