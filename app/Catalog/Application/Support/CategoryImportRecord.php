<?php

declare(strict_types=1);

namespace App\Catalog\Application\Support;

readonly class CategoryImportRecord
{
    public function __construct(
        public string $externalId,
        public ?string $parentExternalId,
        public string $name,
        public int $sortOrder,
        public int $depth,
    ) {}

    /**
     * @return array{
     *     externalId: string,
     *     parentExternalId: string|null,
     *     name: string,
     *     sortOrder: int,
     *     depth: int
     * }
     */
    public function toArray(): array
    {
        return [
            'externalId'       => $this->externalId,
            'parentExternalId' => $this->parentExternalId,
            'name'             => $this->name,
            'sortOrder'        => $this->sortOrder,
            'depth'            => $this->depth,
        ];
    }
}
