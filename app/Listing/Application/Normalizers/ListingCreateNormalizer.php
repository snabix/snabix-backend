<?php

declare(strict_types=1);

namespace App\Listing\Application\Normalizers;

use App\Listing\Application\Support\NormalizedListingData;
use App\Listing\Application\UseCases\CreateListing\CreateListingInput;
use App\Listing\Domain\Enums\ListingStatus;
use Illuminate\Validation\ValidationException;

final readonly class ListingCreateNormalizer
{
    public function __construct(
        private ListingClassificationNormalizer $classificationNormalizer,
        private ListingOwnerFieldsNormalizer $ownerFieldsNormalizer,
        private ListingModerationNormalizer $moderationNormalizer,
    ) {}

    /**
     * @param array<string, mixed> $address
     */
    public function normalize(
        CreateListingInput $input,
        ListingStatus $status,
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
                'user_id'     => $this->userId($input->userId),
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
                ...$this->moderationNormalizer->initialAttributes($status),
            ],
        );
    }

    private function userId(string $userId): string
    {
        $normalized = trim($userId);

        if ($normalized === '') {
            throw ValidationException::withMessages([
                'userId' => ['Пользователь объявления не определён.'],
            ]);
        }

        return $normalized;
    }
}
