<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\DeleteListingMedia;

use App\Listing\Application\Services\ListingMediaService;
use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingReadRepositoryInterface;
use App\Listing\Domain\Events\ListingMediaChanged;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

readonly class DeleteListingMediaHandler
{
    public function __construct(
        private ListingReadRepositoryInterface $listingReader,
        private ListingMediaService $listingMediaService,
        private ListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(DeleteListingMediaInput $input): DeleteListingMediaOutput
    {
        $listing = $this->listingReader->findById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        Gate::authorize('update', $listing);

        $listing = $this->listingMediaService->deleteImage($listing, $input->mediaId);

        event(new ListingMediaChanged($listing, 'delete', [
            'media_id' => $input->mediaId,
        ]));

        return DeleteListingMediaOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
