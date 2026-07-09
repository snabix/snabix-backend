<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Media\Infrastructure\Models\EloquentMedia;
use App\Shared\Application\Support\ReferenceDataCache;

readonly class CategoryIconMediaService
{
    public function __construct(
        private ReferenceDataCache $cache,
    ) {}

    public function replaceFromStoredUpload(EloquentCategory $category, string $sourceDisk, string $sourcePath): EloquentMedia
    {
        $media = $category
            ->addMediaFromDisk($sourcePath, $sourceDisk)
            ->usingName($category->name . ' icon')
            ->toMediaCollection('category_icons', 'public');

        $media->forceFill([
            'description' => 'Иконка категории ' . $category->name,
        ])->save();

        $this->cache->invalidateCatalog();

        return EloquentMedia::query()
            ->whereKey($media->getKey())
            ->firstOrFail();
    }
}
