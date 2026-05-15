<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\UpdateListing;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class UpdateListingHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private ListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(UpdateListingInput $input): UpdateListingOutput
    {
        $listing = $this->listingRepository->findOwnedByUser($input->listingId, $input->userId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        $listing = $this->listingRepository->update(
            $listing,
            attributes: [
                'category_id'      => $input->categoryId,
                'type'             => $input->type,
                'status'           => $input->status,
                'condition'        => $input->condition,
                'title'            => $input->title,
                'description'      => $input->description,
                'price'            => $input->price,
                'currency'         => $input->currency,
                'is_negotiable'    => $input->isNegotiable,
                'contact_name'     => $input->contactName,
                'contact_phone'    => $input->contactPhone,
                'contact_email'    => $input->contactEmail,
                'is_featured'      => $input->isFeatured,
                'rejection_reason' => $input->rejectionReason,
            ],
            attributeValues: $input->attributeValues,
        );

        return UpdateListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
