<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\DeleteListing;

use App\Listing\Domain\Contracts\ListingReadRepositoryInterface;
use App\Listing\Domain\Contracts\ListingWriterInterface;
use App\Listing\Domain\Events\ListingDeleted;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

readonly class DeleteListingHandler
{
    public function __construct(
        private ListingReadRepositoryInterface $listingReader,
        private ListingWriterInterface $listingWriter,
    ) {}

    public function execute(DeleteListingInput $input): DeleteListingOutput
    {
        $listing = $this->listingReader->findById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        Gate::authorize('delete', $listing);

        $event   = new ListingDeleted(
            listingId: $listing->id,
            userId: $listing->user_id,
            title: $listing->title,
            status: $listing->status,
            categoryId: $listing->category_id,
        );

        $this->listingWriter->delete($listing);

        event($event);

        return DeleteListingOutput::from([
            'deleted' => true,
        ]);
    }
}
