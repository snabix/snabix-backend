<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ShowListing;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingReadRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

readonly class ShowListingHandler
{
    public function __construct(
        private ListingReadRepositoryInterface $listingReader,
        private ListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(ShowListingInput $input): ShowListingOutput
    {
        $listing = $this->listingReader->findById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        Gate::authorize('view', $listing);

        return ShowListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
