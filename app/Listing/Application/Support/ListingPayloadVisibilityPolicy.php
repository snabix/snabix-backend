<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;

final readonly class ListingPayloadVisibilityPolicy
{
    /** @var list<string> */
    private const array PUBLIC_FIELDS = [
        'id',
        'category',
        'type',
        'typeLabel',
        'status',
        'statusLabel',
        'condition',
        'conditionLabel',
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'isNegotiable',
        'location',
        'region',
        'city',
        'addressLine',
        'fullLocation',
        'imageUrl',
        'imageUrls',
        'sellerRating',
        'sellerReviewCount',
        'viewsCount',
        'isFeatured',
        'publishedAt',
        'expiresAt',
        'attributeValues',
    ];

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function apply(array $payload, ListingPayloadVisibility $visibility): array
    {
        if ($visibility === ListingPayloadVisibility::PRIVATE_VIEW) {
            return $payload;
        }

        return array_intersect_key(
            $payload,
            array_fill_keys(self::PUBLIC_FIELDS, true),
        );
    }

    public function includesAttributeValue(
        EloquentListingAttributeValue $attributeValue,
        ListingPayloadVisibility $visibility,
    ): bool {
        return $visibility === ListingPayloadVisibility::PRIVATE_VIEW
            || (bool) $attributeValue->attributeDefinition?->show_in_card;
    }

    /**
     * @return list<string>
     */
    public function publicFields(): array
    {
        return self::PUBLIC_FIELDS;
    }
}
