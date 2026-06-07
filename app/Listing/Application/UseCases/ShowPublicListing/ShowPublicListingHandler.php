<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ShowPublicListing;

use App\Listing\Application\Support\PublicListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class ShowPublicListingHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private PublicListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(ShowPublicListingInput $input): ShowPublicListingOutput
    {
        $listing = $this->listingRepository->findPublicPublishedById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        return ShowPublicListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
