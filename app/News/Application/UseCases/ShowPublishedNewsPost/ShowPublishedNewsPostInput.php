<?php

declare(strict_types=1);

namespace App\News\Application\UseCases\ShowPublishedNewsPost;

use App\Shared\Domain\DTO\Input;

class ShowPublishedNewsPostInput extends Input
{
    public function __construct(
        public readonly string $slug,
    ) {}
}
