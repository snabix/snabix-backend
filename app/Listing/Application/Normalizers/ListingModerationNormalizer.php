<?php

declare(strict_types=1);

namespace App\Listing\Application\Normalizers;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;

final readonly class ListingModerationNormalizer
{
    /**
     * @return array{
     *     status: ListingStatus,
     *     views_count: int,
     *     is_featured: bool,
     *     rejection_reason: null,
     *     published_at: null,
     *     expires_at: null
     * }
     */
    public function initialAttributes(ListingStatus $status): array
    {
        return [
            'status'           => $status,
            'views_count'      => 0,
            'is_featured'      => false,
            'rejection_reason' => null,
            'published_at'     => null,
            'expires_at'       => null,
        ];
    }

    /**
     * @return array{status: ListingStatus, rejection_reason: ?string}
     */
    public function statusTransitionAttributes(
        EloquentListing $listing,
        ListingStatus $status,
    ): array {
        return [
            'status'           => $status,
            'rejection_reason' => $status === ListingStatus::PENDING_REVIEW
                ? null
                : $listing->rejection_reason,
        ];
    }
}
