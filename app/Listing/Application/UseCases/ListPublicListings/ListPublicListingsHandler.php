<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListPublicListings;

use App\Listing\Application\Support\PaginationPayloadMapper;
use App\Listing\Application\Support\PublicListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;

readonly class ListPublicListingsHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private PublicListingPayloadMapper $listingPayloadMapper,
        private PaginationPayloadMapper $paginationPayloadMapper,
    ) {}

    public function execute(ListPublicListingsInput $input): ListPublicListingsOutput
    {
        $paginator = $this->listingRepository->listPublicPublished(
            page: $input->page,
            perPage: $input->perPage,
            categoryId: $input->categoryId,
            type: $input->type,
            minPrice: $input->minPrice,
            maxPrice: $input->maxPrice,
            sort: $input->sort,
        );

        return ListPublicListingsOutput::from([
            'items' => $paginator
                ->getCollection()
                ->map(fn(EloquentListing $listing): array => $this->listingPayloadMapper->map($listing))
                ->values()
                ->all(),
            'meta'  => $this->paginationPayloadMapper->map($paginator),
        ]);
    }
}
