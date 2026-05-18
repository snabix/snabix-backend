<?php

declare(strict_types=1);

namespace App\Listing\Domain\Services;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Exceptions\InvalidListingStatusTransitionException;

class ListingStatusTransitionPolicy
{
    /**
     * @return array<int, list<ListingStatus>>
     */
    public function allowedTransitions(): array
    {
        return [
            ListingStatus::DRAFT->value          => [
                ListingStatus::PENDING_REVIEW,
                ListingStatus::ARCHIVED,
            ],
            ListingStatus::PENDING_REVIEW->value => [
                ListingStatus::PUBLISHED,
                ListingStatus::REJECTED,
                ListingStatus::ARCHIVED,
            ],
            ListingStatus::PUBLISHED->value      => [
                ListingStatus::ARCHIVED,
            ],
            ListingStatus::REJECTED->value       => [
                ListingStatus::DRAFT,
                ListingStatus::PENDING_REVIEW,
                ListingStatus::ARCHIVED,
            ],
            ListingStatus::ARCHIVED->value       => [
                ListingStatus::DRAFT,
            ],
        ];
    }

    public function canTransition(
        ListingStatus $from,
        ListingStatus $to,
    ): bool {
        if ($from === $to) {
            return true;
        }

        return in_array($to, $this->allowedTransitions()[$from->value] ?? [], true);
    }

    public function assertCanTransition(
        ListingStatus $from,
        ListingStatus $to,
    ): void {
        if ($this->canTransition($from, $to)) {
            return;
        }

        throw InvalidListingStatusTransitionException::fromStatuses($from, $to);
    }
}
