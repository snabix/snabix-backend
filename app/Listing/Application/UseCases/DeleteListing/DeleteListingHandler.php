<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\DeleteListing;

use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class DeleteListingHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
    ) {}

    public function execute(DeleteListingInput $input): DeleteListingOutput
    {
        $listing = $this->listingRepository->findOwnedByUser($input->listingId, $input->userId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        $this->listingRepository->delete($listing);

        return DeleteListingOutput::from([
            'deleted' => true,
        ]);
    }
}
