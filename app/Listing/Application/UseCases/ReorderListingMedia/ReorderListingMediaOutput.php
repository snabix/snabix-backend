<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ReorderListingMedia;

use App\Shared\Domain\DTO\Output;

class ReorderListingMediaOutput extends Output
{
    /**
     * @param array<string, mixed> $item
     */
    public function __construct(
        public readonly array $item,
    ) {}
}
