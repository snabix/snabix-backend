<?php

declare(strict_types=1);

namespace App\Listing\Domain\Services;

use App\Listing\Domain\Enums\ListingStatus;

class ListingPublicationPolicy
{
    public function statusForUserCreate(bool $saveAsDraft): ListingStatus
    {
        return $saveAsDraft
            ? ListingStatus::DRAFT
            : ListingStatus::PENDING_REVIEW;
    }

    public function shouldValidateRequiredAttributes(ListingStatus $status): bool
    {
        return $status !== ListingStatus::DRAFT;
    }
}
