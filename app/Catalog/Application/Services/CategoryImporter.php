<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Application\Support\ParsedCategoryNode;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CategoryImporter
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * @param  array<int, ParsedCategoryNode>    $nodes
     * @return array{created: int, updated: int}
     *
     * @throws Throwable
     */
    public function import(
        array    $nodes,
        string   $source,
        ?Closure $progressCallback = null,
    ): array {
        $stats = [
            'created' => 0,
            'updated' => 0,
        ];

        DB::transaction(
            function () use ($nodes, $progressCallback, &$stats): void {
                foreach ($nodes as $node) {
                    $this->persistNode($node, null, $stats, $progressCallback);
                }
            },
        );

        return $stats;
    }

    /**
     * @param array{created: int, updated: int} $stats
     */
    private function persistNode(
        ParsedCategoryNode $node,
        ?int               $parentId,
        array              &$stats,
        ?Closure           $progressCallback = null,
    ): void {
        $category      = $this->categoryRepository->findByParentAndName($parentId, $node->name);

        if ($category === null) {
            $stats['created']++;
        } else {
            $stats['updated']++;
        }

        $savedCategory = $this->categoryRepository->save(
            [
                'parent_id'  => $parentId,
                'name'       => $node->name,
                'sort_order' => $node->sortOrder,
                'is_active'  => true,
            ],
            $category?->id,
        );

        $progressCallback?->__invoke($node, $savedCategory);

        foreach ($node->children as $child) {
            $this->persistNode(
                node: $child,
                parentId: $savedCategory->id,
                stats: $stats,
                progressCallback: $progressCallback,
            );
        }
    }
}
