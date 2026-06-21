<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\UpdateListing;

use App\Listing\Application\Normalizers\ListingUpdateNormalizer;
use App\Listing\Application\Services\ListingAddressSnapshotService;
use App\Listing\Application\Services\ListingRequiredAttributeValidator;
use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingReadRepositoryInterface;
use App\Listing\Domain\Contracts\ListingWriterInterface;
use App\Listing\Domain\Events\ListingUpdated;
use App\Listing\Domain\Services\ListingPublicationPolicy;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

readonly class UpdateListingHandler
{
    public function __construct(
        private ListingReadRepositoryInterface $listingReader,
        private ListingWriterInterface $listingWriter,
        private ListingPayloadMapper $listingPayloadMapper,
        private ListingPublicationPolicy $listingPublicationPolicy,
        private ListingRequiredAttributeValidator $listingRequiredAttributeValidator,
        private ListingAddressSnapshotService $listingAddressSnapshotService,
        private ListingUpdateNormalizer $listingUpdateNormalizer,
    ) {}

    public function execute(UpdateListingInput $input): UpdateListingOutput
    {
        $listing       = $this->listingReader->findById($input->listingId);

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

        $address       = $this->listingAddressSnapshotService->resolve($input->userId, [
            'addressMode'      => $input->addressMode,
            'profileAddressId' => $input->profileAddressId,
            'regionId'         => $input->regionId,
            'cityId'           => $input->cityId,
            'addressLine'      => $input->addressLine,
        ]);

        $listing       = $this->listingWriter->update(
            $listing,
            data: $this->listingUpdateNormalizer->normalize($input, $address),
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
            'category_id'       => $listing->category_id,
            'type'              => $listing->type->value,
            'condition'         => $listing->condition->value,
            'title'             => $listing->title,
            'description'       => $listing->description,
            'price'             => $listing->price,
            'currency'          => $listing->currency,
            'is_negotiable'     => $listing->is_negotiable,
            'contact_name'      => $listing->contact_name,
            'contact_phone'     => $listing->contact_phone,
            'contact_email'     => $listing->contact_email,
            'profile_address_id'=> $listing->profile_address_id,
            'region_id'         => $listing->region_id,
            'city_id'           => $listing->city_id,
            'address_snapshot'  => $listing->address_snapshot,
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
