<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListListings;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;

readonly class ListListingsHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private ListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(ListListingsInput $input): ListListingsOutput
    {
        return ListListingsOutput::from([
            'items' => $this->listingRepository
                ->listOwnedByUser($input->userId)
                ->map(fn(EloquentListing $listing): array => $this->listingPayloadMapper->map($listing))
                ->values()
                ->all(),
        ]);
    }
}
