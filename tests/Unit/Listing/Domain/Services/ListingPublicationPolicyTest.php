<?php

declare(strict_types=1);

namespace Tests\Unit\Listing\Domain\Services;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Services\ListingPublicationPolicy;
use PHPUnit\Framework\TestCase;

class ListingPublicationPolicyTest extends TestCase
{
    public function test_user_create_status_depends_on_explicit_draft_flag(): void
    {
        $policy = new ListingPublicationPolicy();

        $this->assertSame(ListingStatus::DRAFT, $policy->statusForUserCreate(true));
        $this->assertSame(ListingStatus::PENDING_REVIEW, $policy->statusForUserCreate(false));
    }

    public function test_required_attributes_are_optional_only_for_drafts(): void
    {
        $policy = new ListingPublicationPolicy();

        $this->assertFalse($policy->shouldValidateRequiredAttributes(ListingStatus::DRAFT));
        $this->assertTrue($policy->shouldValidateRequiredAttributes(ListingStatus::PENDING_REVIEW));
        $this->assertTrue($policy->shouldValidateRequiredAttributes(ListingStatus::PUBLISHED));
    }
}
