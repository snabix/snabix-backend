<?php

declare(strict_types=1);

namespace Tests\Unit\Listing\Application\Normalizers;

use App\Listing\Application\Normalizers\ListingModerationNormalizer;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use PHPUnit\Framework\TestCase;

class ListingModerationNormalizerTest extends TestCase
{
    public function test_initial_attributes_are_safe_for_user_created_listing(): void
    {
        $attributes = (new ListingModerationNormalizer())->initialAttributes(
            ListingStatus::PENDING_REVIEW,
        );

        $this->assertSame([
            'status'           => ListingStatus::PENDING_REVIEW,
            'views_count'      => 0,
            'is_featured'      => false,
            'rejection_reason' => null,
            'published_at'     => null,
            'expires_at'       => null,
        ], $attributes);
    }

    public function test_pending_review_transition_clears_rejection_reason(): void
    {
        $listing                   = new EloquentListing();
        $listing->rejection_reason = 'Исправьте описание';

        $attributes                = (new ListingModerationNormalizer())->statusTransitionAttributes(
            $listing,
            ListingStatus::PENDING_REVIEW,
        );

        $this->assertSame([
            'status'           => ListingStatus::PENDING_REVIEW,
            'rejection_reason' => null,
        ], $attributes);
    }
}
