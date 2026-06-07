<?php

declare(strict_types=1);

namespace App\News\Application\UseCases\ListPublishedNewsPosts;

use App\Shared\Domain\DTO\Output;

class ListPublishedNewsPostsOutput extends Output
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<string, int>               $meta
     */
    public function __construct(
        public readonly array $items,
        public readonly array $meta,
    ) {}
}
