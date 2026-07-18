<?php

declare(strict_types=1);

namespace App\Catalog\Application\Support;

readonly class CategorySourceDocument
{
    public function __construct(
        public string $source,
        public string $version,
        public string $sourceUrl,
        public string $contents,
    ) {}
}
