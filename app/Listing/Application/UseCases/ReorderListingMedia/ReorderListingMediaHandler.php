<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ReorderListingMedia;

use App\Listing\Application\Services\ListingMediaService;
use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Events\ListingMediaChanged;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

readonly class ReorderListingMediaHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private ListingMediaService $listingMediaService,
        private ListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(ReorderListingMediaInput $input): ReorderListingMediaOutput
    {
        $listing = $this->listingRepository->findById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        Gate::authorize('update', $listing);

        $listing = $this->listingMediaService->reorderImages($listing, $input->mediaIds);

        event(new ListingMediaChanged($listing, 'reorder', [
            'media_ids' => $input->mediaIds,
        ]));

        return ReorderListingMediaOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
