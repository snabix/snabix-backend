<?php

declare(strict_types=1);

namespace Tests\Unit\Listing\Domain\Services;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Exceptions\InvalidListingStatusTransitionException;
use App\Listing\Domain\Services\ListingStatusTransitionPolicy;
use PHPUnit\Framework\TestCase;

class ListingStatusTransitionPolicyTest extends TestCase
{
    public function test_same_status_transition_is_allowed(): void
    {
        $policy = new ListingStatusTransitionPolicy();

        $this->assertTrue($policy->canTransition(ListingStatus::DRAFT, ListingStatus::DRAFT));
    }

    public function test_draft_can_be_submitted_for_review(): void
    {
        $policy = new ListingStatusTransitionPolicy();

        $this->assertTrue($policy->canTransition(ListingStatus::DRAFT, ListingStatus::PENDING_REVIEW));
    }

    public function test_published_listing_cannot_return_directly_to_pending_review(): void
    {
        $policy = new ListingStatusTransitionPolicy();

        $this->assertFalse($policy->canTransition(ListingStatus::PUBLISHED, ListingStatus::PENDING_REVIEW));
    }

    public function test_invalid_transition_throws_domain_exception(): void
    {
        $policy = new ListingStatusTransitionPolicy();

        $this->expectException(InvalidListingStatusTransitionException::class);

        $policy->assertCanTransition(ListingStatus::PUBLISHED, ListingStatus::PENDING_REVIEW);
    }
}
