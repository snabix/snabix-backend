<?php

declare(strict_types=1);

namespace App\Listing\Domain\Exceptions;

use App\Listing\Domain\Enums\ListingStatus;
use DomainException;

class InvalidListingStatusTransitionException extends DomainException
{
    public static function fromStatuses(
        ListingStatus $from,
        ListingStatus $to,
    ): self {
        return new self(sprintf(
            'Listing status transition from "%s" to "%s" is not allowed.',
            $from->name,
            $to->name,
        ));
    }
}
