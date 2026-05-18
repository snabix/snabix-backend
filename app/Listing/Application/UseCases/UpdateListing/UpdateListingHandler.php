<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\UpdateListing;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Services\ListingPublicationPolicy;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

readonly class UpdateListingHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private ListingPayloadMapper $listingPayloadMapper,
        private ListingPublicationPolicy $listingPublicationPolicy,
    ) {}

    public function execute(UpdateListingInput $input): UpdateListingOutput
    {
        $listing = $this->listingRepository->findById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        Gate::authorize('update', $listing);

        $listing = $this->listingRepository->update(
            $listing,
            attributes: [
                'category_id'      => $input->categoryId,
                'type'             => $input->type,
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
            validateRequiredAttributes: $this->listingPublicationPolicy->shouldValidateRequiredAttributes($listing->status),
        );

        return UpdateListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
