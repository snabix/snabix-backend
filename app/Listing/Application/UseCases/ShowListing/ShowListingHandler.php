<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ShowListing;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class ShowListingHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private ListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(ShowListingInput $input): ShowListingOutput
    {
        $listing = $this->listingRepository->findOwnedByUser($input->listingId, $input->userId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        return ShowListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
