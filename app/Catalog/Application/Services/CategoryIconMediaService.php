<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Media\Application\Services\MediaStorageService;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Infrastructure\Models\EloquentMedia;
use Throwable;

readonly class CategoryIconMediaService
{
    public function __construct(
        private MediaStorageService $mediaStorageService,
    ) {}

    /**
     * @throws Throwable
     */
    public function replaceFromStoredUpload(EloquentCategory $category, string $sourceDisk, string $sourcePath): EloquentMedia
    {
        $previousIcon = $category->getFirstMedia('category_icons');

        $media        = $this->mediaStorageService->createFromStoredUpload(
            sourceDisk: $sourceDisk,
            sourcePath: $sourcePath,
            attributes: [
                'model_type'      => EloquentCategory::class,
                'model_id'        => $category->id,
                'collection_name' => 'category_icons',
                'name'            => $category->name . ' icon',
                'media_type'      => MediaType::IMAGE,
                'visibility'      => MediaVisibility::PUBLIC,
                'disk'            => MediaVisibility::PUBLIC->disk(),
                'description'     => 'Иконка категории ' . $category->name,
            ],
        );

        if ($previousIcon instanceof EloquentMedia && $previousIcon->isNot($media)) {
            $previousIcon->delete();
        }

        return $media;
    }
}
