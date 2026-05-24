<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Domain\Enums\ListingStatus;
use LogicException;

readonly class NormalizedListingData
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public EloquentCategory $category,
        public array            $attributes,
    ) {}

    public function title(): string
    {
        $title = $this->attributes['title'] ?? null;

        if (! is_string($title)) {
            throw new LogicException('Normalized listing title must be a string.');
        }

        return $title;
    }

    public function status(): ListingStatus
    {
        $status = $this->attributes['status'] ?? null;

        if (! $status instanceof ListingStatus) {
            throw new LogicException('Normalized listing status must be a ListingStatus enum.');
        }

        return $status;
    }
}
