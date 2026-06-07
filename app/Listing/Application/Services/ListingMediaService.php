<?php

declare(strict_types=1);

namespace App\Listing\Application\Services;

use App\Listing\Infrastructure\Models\EloquentListing;
use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

readonly class ListingMediaService
{
    public const string COLLECTION_NAME = 'listing-images';

    public const int MAX_IMAGES         = 8;

    /**
     * @param  list<UploadedFile> $images
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
            $media = $listing
                ->addMedia($image)
                ->usingName(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
                ->usingFileName($image->getClientOriginalName())
                ->toMediaCollection(self::COLLECTION_NAME, 'public');

            $media->forceFill([
                'media_type'   => MediaType::IMAGE,
                'description'  => 'Listing image.',
                'order_column' => $existingImagesCount + $index + 1,
            ])->save();
        }

        return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'media']) ?? $listing;
    }

    public function deleteImage(EloquentListing $listing, int $mediaId): EloquentListing
    {
        $media = $this->listingImageQuery($listing)
            ->whereKey($mediaId)
            ->first();

        if (! $media instanceof EloquentMedia) {
            throw (new ModelNotFoundException())->setModel(EloquentMedia::class, [$mediaId]);
        }

        $media->delete();
        $this->normalizeOrder($listing);

        return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'media']) ?? $listing;
    }

    /**
     * @param list<int> $mediaIds
     */
    public function reorderImages(EloquentListing $listing, array $mediaIds): EloquentListing
    {
        return DB::transaction(function () use ($listing, $mediaIds): EloquentListing {
            $existingIds        = $this->listingImageQuery($listing)
                ->pluck('id')
                ->all();
            $existingIds        = $this->normalizeIntegerIds($existingIds);

            $uniqueMediaIds     = array_values(array_unique($mediaIds));

            sort($existingIds);
            $sortedRequestedIds = $uniqueMediaIds;
            sort($sortedRequestedIds);

            if ($existingIds !== $sortedRequestedIds) {
                throw ValidationException::withMessages([
                    'mediaIds' => ['Передайте полный список изображений объявления в новом порядке.'],
                ]);
            }

            foreach ($uniqueMediaIds as $index => $mediaId) {
                $this->listingImageQuery($listing)
                    ->whereKey($mediaId)
                    ->update(['order_column' => $index + 1]);
            }

            return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'media']) ?? $listing;
        });
    }

    public function setMainImage(EloquentListing $listing, int $mediaId): EloquentListing
    {
        $mediaIds = $this->listingImageQuery($listing)
            ->pluck('id')
            ->all();
        $mediaIds = $this->normalizeIntegerIds($mediaIds);

        if (! in_array($mediaId, $mediaIds, true)) {
            throw (new ModelNotFoundException())->setModel(EloquentMedia::class, [$mediaId]);
        }

        return $this->reorderImages($listing, [
            $mediaId,
            ...array_values(array_filter($mediaIds, fn(int $id): bool => $id !== $mediaId)),
        ]);
    }

    private function normalizeOrder(EloquentListing $listing): void
    {
        $this->listingImageQuery($listing)
            ->get()
            ->values()
            ->each(function (EloquentMedia $media, int $index): void {
                $media->forceFill(['order_column' => $index + 1])->save();
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<EloquentMedia>
     */
    private function listingImageQuery(EloquentListing $listing): \Illuminate\Database\Eloquent\Builder
    {
        return EloquentMedia::query()
            ->where('model_type', EloquentListing::class)
            ->where('model_id', $listing->id)
            ->where('collection_name', self::COLLECTION_NAME)
            ->where('media_type', MediaType::IMAGE)
            ->orderBy('order_column')
            ->orderBy('id');
    }

    /**
     * @param  array<int, mixed> $ids
     * @return list<int>
     */
    private function normalizeIntegerIds(array $ids): array
    {
        $normalizedIds = [];

        foreach ($ids as $id) {
            if (is_int($id)) {
                $normalizedIds[] = $id;

                continue;
            }

            if (is_string($id) && ctype_digit($id)) {
                $normalizedIds[] = (int) $id;
            }
        }

        return $normalizedIds;
    }
}
