<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\UpdateListing;

use App\Shared\Domain\DTO\Input;

class UpdateListingInput extends Input
{
    /**
     * @param array<array-key, mixed> $attributeValues
     */
    public function __construct(
        public readonly string $userId,
        public readonly string $listingId,
        public readonly int $categoryId,
        public readonly int $type,
        public readonly ?int $condition,
        public readonly string $title,
        public readonly string $description,
        public readonly ?int $price,
        public readonly ?string $currency,
        public readonly bool $isNegotiable,
        public readonly ?string $contactName,
        public readonly ?string $contactPhone,
        public readonly ?string $contactEmail,
        public readonly array $attributeValues,
    ) {}
}
