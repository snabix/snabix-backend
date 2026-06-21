<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingType;

final readonly class NormalizedListingClassification
{
    public function __construct(
        public EloquentCategory $category,
        public ListingType $type,
        public ListingCondition $condition,
    ) {}
}
