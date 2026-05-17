<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListPublicListings;

use App\Listing\Application\Support\PublicListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;

readonly class ListPublicListingsHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private PublicListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(ListPublicListingsInput $input): ListPublicListingsOutput
    {
        return ListPublicListingsOutput::from([
            'items' => $this->listingRepository
                ->listPublicPublished($input->limit)
                ->map(fn(EloquentListing $listing): array => $this->listingPayloadMapper->map($listing))
                ->values()
                ->all(),
        ]);
    }
}
