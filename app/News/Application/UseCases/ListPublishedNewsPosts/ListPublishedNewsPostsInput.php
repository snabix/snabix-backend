<?php

declare(strict_types=1);

namespace App\News\Application\UseCases\ListPublishedNewsPosts;

use App\Shared\Domain\DTO\Input;

class ListPublishedNewsPostsInput extends Input
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 12,
        public readonly ?string $category = null,
        public readonly bool $featuredOnly = false,
    ) {}
}
