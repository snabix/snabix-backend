<?php

declare(strict_types=1);

namespace App\Catalog\Application\Support;

readonly class ParsedCategoryNode
{
    /**
     * @param array<int, self> $children
     */
    public function __construct(
        public string $externalId,
        public string $name,
        public int $sortOrder,
        public array $children = [],
    ) {}

    /**
     * @return array{externalId: string, name: string, sortOrder: int, children: array<int, array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'externalId'=> $this->externalId,
            'name'      => $this->name,
            'sortOrder' => $this->sortOrder,
            'children'  => array_map(
                static fn(self $child): array => $child->toArray(),
                $this->children,
            ),
        ];
    }
}
