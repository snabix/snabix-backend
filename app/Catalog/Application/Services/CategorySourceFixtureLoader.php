<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Application\Support\CategorySourceDocument;
use RuntimeException;

readonly class CategorySourceFixtureLoader
{
    public function __construct(
        private CatalogImportSourcePolicy $sourcePolicy,
    ) {}

    public function load(string $source, string $version, string $path): CategorySourceDocument
    {
        $authorizedPath = $this->sourcePolicy->authorizeFixturePath($path);
        $size           = filesize($authorizedPath);

        if ($size === false || $size > $this->sourcePolicy->maxResponseBytes()) {
            throw new RuntimeException('Fixture категорий превышает допустимый размер.');
        }

        $contents       = file_get_contents($authorizedPath);

        if (! is_string($contents) || $contents === '') {
            throw new RuntimeException('Fixture категорий пуста или не читается.');
        }

        return new CategorySourceDocument(
            source: $source,
            version: $version,
            sourceUrl: 'fixture://' . basename($authorizedPath),
            contents: $contents,
        );
    }
}
