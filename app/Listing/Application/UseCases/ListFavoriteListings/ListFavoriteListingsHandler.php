<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListFavoriteListings;

use App\Listing\Application\Services\ListingFavoriteService;
use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Application\Support\PaginationPayloadMapper;
use App\Listing\Infrastructure\Models\EloquentListing;

readonly class ListFavoriteListingsHandler
{
    public function __construct(
        private ListingFavoriteService $listingFavoriteService,
        private ListingPayloadMapper $listingPayloadMapper,
        private PaginationPayloadMapper $paginationPayloadMapper,
    ) {}

    public function execute(ListFavoriteListingsInput $input): ListFavoriteListingsOutput
    {
        $paginator = $this->listingFavoriteService->list(
            userId: $input->userId,
            page: $input->page,
            perPage: $input->perPage,
        );

        return ListFavoriteListingsOutput::from([
            'items' => $paginator
                ->getCollection()
                ->map(fn(EloquentListing $listing): array => [
                    ...$this->listingPayloadMapper->map($listing),
                    'isFavorite' => true,
                ])
                ->values()
                ->all(),
            'meta'  => $this->paginationPayloadMapper->map($paginator),
        ]);
    }
}
