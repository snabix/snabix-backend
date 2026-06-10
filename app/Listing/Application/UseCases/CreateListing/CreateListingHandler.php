<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\CreateListing;

use App\Listing\Application\Services\ListingAddressSnapshotService;
use App\Listing\Application\Services\ListingRequiredAttributeValidator;
use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Events\ListingCreated;
use App\Listing\Domain\Services\ListingPublicationPolicy;

readonly class CreateListingHandler
{
    public function __construct(
        private ListingRepositoryInterface        $listingRepository,
        private ListingPayloadMapper              $listingPayloadMapper,
        private ListingPublicationPolicy          $listingPublicationPolicy,
        private ListingRequiredAttributeValidator $listingRequiredAttributeValidator,
        private ListingAddressSnapshotService     $listingAddressSnapshotService,
    ) {}

    public function execute(CreateListingInput $input): CreateListingOutput
    {
        $status  = $this->listingPublicationPolicy->statusForUserCreate($input->saveAsDraft);

        if ($this->listingPublicationPolicy->shouldValidateRequiredAttributes($status)) {
            $this->listingRequiredAttributeValidator->validateSubmittedValues(
                categoryId: $input->categoryId,
                attributeValues: $input->attributeValues,
            );
        }

        $address = $this->listingAddressSnapshotService->resolve(
            $input->userId,
            [
                'addressMode'      => $input->addressMode,
                'profileAddressId' => $input->profileAddressId,
                'regionId'         => $input->regionId,
                'cityId'           => $input->cityId,
                'addressLine'      => $input->addressLine,
            ],
        );

        $listing = $this->listingRepository->create(
            attributes: [
                'user_id'       => $input->userId,
                'category_id'   => $input->categoryId,
                'type'          => $input->type,
                'status'        => $status,
                'condition'     => $input->condition,
                'title'         => $input->title,
                'description'   => $input->description,
                'price'         => $input->price,
                'currency'      => $input->currency,
                'is_negotiable' => $input->isNegotiable,
                'contact_name'  => $input->contactName,
                'contact_phone' => $input->contactPhone,
                'contact_email' => $input->contactEmail,
                ...$address,
            ],
            attributeValues: $input->attributeValues,
        );

        event(new ListingCreated($listing));

        return CreateListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
