<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\CreateListing;

use App\Listing\Application\Normalizers\ListingCreateNormalizer;
use App\Listing\Application\Services\ListingAddressSnapshotService;
use App\Listing\Application\Services\ListingRequiredAttributeValidator;
use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingWriterInterface;
use App\Listing\Domain\Events\ListingCreated;
use App\Listing\Domain\Services\ListingPublicationPolicy;

readonly class CreateListingHandler
{
    public function __construct(
        private ListingWriterInterface            $listingWriter,
        private ListingPayloadMapper              $listingPayloadMapper,
        private ListingPublicationPolicy          $listingPublicationPolicy,
        private ListingRequiredAttributeValidator $listingRequiredAttributeValidator,
        private ListingAddressSnapshotService     $listingAddressSnapshotService,
        private ListingCreateNormalizer            $listingCreateNormalizer,
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

        $listing = $this->listingWriter->create(
            data: $this->listingCreateNormalizer->normalize($input, $status, $address),
            attributeValues: $input->attributeValues,
        );

        event(new ListingCreated($listing));

        return CreateListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
