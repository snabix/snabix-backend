<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

use App\Catalog\Infrastructure\Models\EloquentCategory;
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
}
