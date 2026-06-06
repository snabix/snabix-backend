<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\UpdateListing;

use App\Listing\Application\Services\ListingRequiredAttributeValidator;
use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Events\ListingUpdated;
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
        private ListingRequiredAttributeValidator $listingRequiredAttributeValidator,
    ) {}

    public function execute(UpdateListingInput $input): UpdateListingOutput
    {
        $listing       = $this->listingRepository->findById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        Gate::authorize('update', $listing);

        if ($this->listingPublicationPolicy->shouldValidateRequiredAttributes($listing->status)) {
            $this->listingRequiredAttributeValidator->validateSubmittedValues(
                categoryId: $input->categoryId,
                attributeValues: $input->attributeValues,
            );
        }

        $beforeChanges = $this->listingSnapshot($listing);

        $listing       = $this->listingRepository->update(
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
        );

        $changes       = $this->changedFields($beforeChanges, $this->listingSnapshot($listing));

        if ($changes !== []) {
            event(new ListingUpdated($listing, $changes));
        }

        return UpdateListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function listingSnapshot(EloquentListing $listing): array
    {
        return [
            'category_id'   => $listing->category_id,
            'type'          => $listing->type->value,
            'condition'     => $listing->condition->value,
            'title'         => $listing->title,
            'description'   => $listing->description,
            'price'         => $listing->price,
            'currency'      => $listing->currency,
            'is_negotiable' => $listing->is_negotiable,
            'contact_name'  => $listing->contact_name,
            'contact_phone' => $listing->contact_phone,
            'contact_email' => $listing->contact_email,
        ];
    }

    /**
     * @param  array<string, mixed>                         $before
     * @param  array<string, mixed>                         $after
     * @return array<string, array{from: mixed, to: mixed}>
     */
    private function changedFields(array $before, array $after): array
    {
        $changes = [];

        foreach ($after as $field => $afterValue) {
            $beforeValue     = $before[$field] ?? null;

            if ($beforeValue === $afterValue) {
                continue;
            }

            $changes[$field] = [
                'from' => $beforeValue,
                'to'   => $afterValue,
            ];
        }

        return $changes;
    }
}
