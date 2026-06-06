<?php

declare(strict_types=1);

namespace App\News\Application\UseCases\ShowPublishedNewsPost;

use App\Shared\Domain\DTO\Output;

class ShowPublishedNewsPostOutput extends Output
{
    /**
     * @param array<string, mixed> $item
     */
    public function __construct(
        public readonly array $item,
    ) {}
}
