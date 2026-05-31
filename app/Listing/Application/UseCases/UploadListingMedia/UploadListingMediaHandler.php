<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\UploadListingMedia;

use App\Listing\Application\Services\ListingMediaService;
use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Events\ListingMediaChanged;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Throwable;

readonly class UploadListingMediaHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private ListingMediaService $listingMediaService,
        private ListingPayloadMapper $listingPayloadMapper,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(UploadListingMediaInput $input): UploadListingMediaOutput
    {
        $listing          = $this->listingRepository->findById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        Gate::authorize('update', $listing);

        $previousMediaIds = $this->normalizeIntegerIds($listing->media->pluck('id')->all());
        $listing          = $this->listingMediaService->uploadImages($listing, $input->images);
        $uploadedMediaIds = collect($this->normalizeIntegerIds($listing->media->pluck('id')->all()))
            ->diff($previousMediaIds)
            ->values()
            ->all();

        event(new ListingMediaChanged($listing, 'upload', [
            'media_ids' => $uploadedMediaIds,
            'count'     => count($uploadedMediaIds),
        ]));

        return UploadListingMediaOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
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
