<?php

declare(strict_types=1);

namespace App\Listing\Application\Normalizers;

use App\Listing\Application\Support\NormalizedListingData;
use App\Listing\Application\UseCases\UpdateListing\UpdateListingInput;

final readonly class ListingUpdateNormalizer
{
    public function __construct(
        private ListingClassificationNormalizer $classificationNormalizer,
        private ListingOwnerFieldsNormalizer $ownerFieldsNormalizer,
    ) {}

    /**
     * @param array<string, mixed> $address
     */
    public function normalize(
        UpdateListingInput $input,
        array $address,
    ): NormalizedListingData {
        $classification = $this->classificationNormalizer->normalize(
            categoryId: $input->categoryId,
            type: $input->type,
            condition: $input->condition,
        );

        return new NormalizedListingData(
            category: $classification->category,
            attributes: [
                'category_id' => $classification->category->id,
                'type'        => $classification->type,
                'condition'   => $classification->condition,
                ...$this->ownerFieldsNormalizer->normalize([
                    'title'          => $input->title,
                    'description'    => $input->description,
                    'price'          => $input->price,
                    'currency'       => $input->currency,
                    'is_negotiable'  => $input->isNegotiable,
                    'contact_name'   => $input->contactName,
                    'contact_phone'  => $input->contactPhone,
                    'contact_email'  => $input->contactEmail,
                ], $address),
            ],
        );
    }
}
