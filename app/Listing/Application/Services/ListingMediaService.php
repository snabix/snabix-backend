<?php

declare(strict_types=1);

namespace App\Listing\Application\Services;

use App\Listing\Infrastructure\Models\EloquentListing;
use App\Media\Application\Services\MediaStorageService;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Throwable;

readonly class ListingMediaService
{
    public const string COLLECTION_NAME = 'listing-images';

    public const int MAX_IMAGES         = 8;

    public function __construct(
        private MediaStorageService $mediaStorageService,
    ) {}

    /**
     * @param  list<UploadedFile> $images
     * @throws Throwable
     */
    public function uploadImages(EloquentListing $listing, array $images): EloquentListing
    {
        $existingImagesCount = EloquentMedia::query()
            ->where('model_type', EloquentListing::class)
            ->where('model_id', $listing->id)
            ->where('collection_name', self::COLLECTION_NAME)
            ->count();

        if ($existingImagesCount + count($images) > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'images' => ['У объявления может быть не больше ' . self::MAX_IMAGES . ' изображений.'],
            ]);
        }

        foreach ($images as $index => $image) {
            $storedPath = $image->store('listing-media-temp/' . $listing->id, 'local');

            if (! is_string($storedPath)) {
                throw ValidationException::withMessages([
                    'images.' . $index => ['Не удалось сохранить временный файл изображения.'],
                ]);
            }

            $media      = $this->mediaStorageService->createFromStoredUpload('local', $storedPath, [
                'model_type'      => EloquentListing::class,
                'model_id'        => $listing->id,
                'collection_name' => self::COLLECTION_NAME,
                'name'            => pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME),
                'media_type'      => MediaType::IMAGE,
                'visibility'      => MediaVisibility::PUBLIC,
                'description'     => 'Listing image.',
            ]);

            $media->forceFill([
                'order_column' => $existingImagesCount + $index + 1,
            ])->save();
        }

        return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'media']) ?? $listing;
    }
}
