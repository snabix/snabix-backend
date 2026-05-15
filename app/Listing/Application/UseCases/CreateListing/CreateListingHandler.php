<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\CreateListing;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;

readonly class CreateListingHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private ListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(CreateListingInput $input): CreateListingOutput
    {
        $listing = $this->listingRepository->create(
            attributes: [
                'user_id'          => $input->userId,
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

        return CreateListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
