<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\CreateListing;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Enums\ListingStatus;

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
                'status'           => ListingStatus::DRAFT,
                'condition'        => $input->condition,
                'title'            => $input->title,
                'description'      => $input->description,
                'price'            => $input->price,
                'currency'         => $input->currency,
                'is_negotiable'    => $input->isNegotiable,
                'contact_name'     => $input->contactName,
                'contact_phone'    => $input->contactPhone,
                'contact_email'    => $input->contactEmail,
            ],
            attributeValues: $input->attributeValues,
        );

        return CreateListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
