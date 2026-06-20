<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListListings;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Application\Support\PaginationPayloadMapper;
use App\Listing\Domain\Contracts\OwnedListingQueryInterface;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;

readonly class ListListingsHandler
{
    public function __construct(
        private OwnedListingQueryInterface $ownedListingQuery,
        private ListingPayloadMapper $listingPayloadMapper,
        private PaginationPayloadMapper $paginationPayloadMapper,
    ) {}

    public function execute(ListListingsInput $input): ListListingsOutput
    {
        $paginator = $this->ownedListingQuery->listOwnedByUser(
            userId: $input->userId,
            page: $input->page,
            perPage: $input->perPage,
            status: $input->status !== null ? ListingStatus::tryFrom($input->status) : null,
            type: $input->type,
            categoryId: $input->categoryId,
        );

        return ListListingsOutput::from([
            'items' => $paginator
                ->getCollection()
                ->map(fn(EloquentListing $listing): array => $this->listingPayloadMapper->map($listing))
                ->values()
                ->all(),
            'meta'  => $this->paginationPayloadMapper->map($paginator),
        ]);
    }
}
