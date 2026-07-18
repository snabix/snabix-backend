<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Application\Support\ParsedCategoryNode;
use App\Catalog\Infrastructure\Models\EloquentCategoryImportManifest;

readonly class CategoryImporter
{
    public function __construct(
        private CategoryImportPlanner $planner,
        private CategoryImportExecutor $executor,
    ) {}

    /**
     * @param array<int, ParsedCategoryNode> $nodes
     */
    public function preview(
        array $nodes,
        string $source,
        string $sourceVersion,
        ?string $sourceUrl,
    ): EloquentCategoryImportManifest {
        return $this->planner->preview(
            nodes: $nodes,
            source: $source,
            sourceVersion: $sourceVersion,
            sourceUrl: $sourceUrl,
        );
    }

    public function apply(string $manifestId): EloquentCategoryImportManifest
    {
        return $this->executor->apply($manifestId);
    }

    public function rollback(string $manifestId): EloquentCategoryImportManifest
    {
        return $this->executor->rollback($manifestId);
    }
}
