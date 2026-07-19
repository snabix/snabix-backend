<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Application\Support\CategoryImportChange;
use App\Catalog\Application\Support\CategoryImportRecord;
use App\Catalog\Application\Support\ParsedCategoryNode;
use App\Catalog\Domain\Enums\CategoryImportAction;
use App\Catalog\Domain\Enums\CategoryImportStatus;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryImportManifest;
use InvalidArgumentException;

readonly class CategoryImportPlanner
{
    public function __construct(
        private CategoryImportStateService $stateService,
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
        $source               = $this->requiredIdentifier($source, 'source');
        $sourceVersion        = $this->requiredIdentifier($sourceVersion, 'source version');
        $records              = $this->flatten($nodes);

        if ($records === []) {
            throw new InvalidArgumentException('Источник не содержит категорий для импорта.');
        }

        ksort($records);

        $existingByExternalId = $this->stateService->existingByExternalId($source);
        $changes              = [];
        $stats                = [
            'created'     => 0,
            'updated'     => 0,
            'deactivated' => 0,
            'unchanged'   => 0,
        ];

        foreach ($records as $externalId => $record) {
            $existing  = $existingByExternalId[$externalId] ?? null;
            $after     = $this->recordState($record, $existing);

            if ($existing === null) {
                $changes[] = new CategoryImportChange(
                    action: CategoryImportAction::CREATE,
                    externalId: $externalId,
                    depth: $record->depth,
                    before: null,
                    after: $after,
                );
                $stats['created']++;

                continue;
            }

            $before    = $this->stateService->categoryState($existing);

            if ($this->stateService->statesMatch($before, $after)) {
                $stats['unchanged']++;

                continue;
            }

            $changes[] = new CategoryImportChange(
                action: CategoryImportAction::UPDATE,
                externalId: $externalId,
                depth: $record->depth,
                before: $before,
                after: $after,
            );
            $stats['updated']++;
        }

        foreach ($existingByExternalId as $externalId => $existing) {
            if (isset($records[$externalId])) {
                continue;
            }

            if (! $existing->is_active) {
                $stats['unchanged']++;

                continue;
            }

            $before              = $this->stateService->categoryState($existing);
            $after               = $before;
            $after['isActive']   = false;
            $changes[]           = new CategoryImportChange(
                action: CategoryImportAction::DEACTIVATE,
                externalId: $externalId,
                depth: $existing->depth,
                before: $before,
                after: $after,
            );
            $stats['deactivated']++;
        }

        $serializedRecords    = array_map(
            static fn(CategoryImportRecord $record): array => $record->toArray(),
            array_values($records),
        );
        $checksumPayload      = json_encode(
            [
                'source'        => $source,
                'sourceVersion' => $sourceVersion,
                'records'       => $serializedRecords,
            ],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        return EloquentCategoryImportManifest::query()->create([
            'source'         => $source,
            'source_version' => $sourceVersion,
            'source_url'     => $sourceUrl,
            'checksum'       => hash('sha256', $checksumPayload),
            'status'         => CategoryImportStatus::PREVIEW,
            'records'        => $serializedRecords,
            'diff'           => array_map(
                static fn(CategoryImportChange $change): array => $change->toArray(),
                $changes,
            ),
            'stats'          => $stats,
        ]);
    }

    /**
     * @param  array<int, ParsedCategoryNode>      $nodes
     * @return array<string, CategoryImportRecord>
     */
    private function flatten(array $nodes): array
    {
        $records = [];

        $walk    = function (
            array $currentNodes,
            ?string $parentExternalId,
            int $depth,
        ) use (&$walk, &$records): void {
            foreach ($currentNodes as $node) {
                if (! $node instanceof ParsedCategoryNode) {
                    throw new InvalidArgumentException('Category parser returned an invalid node.');
                }

                $externalId           = trim($node->externalId);
                $name                 = trim($node->name);

                if ($externalId === '' || mb_strlen($externalId) > 512) {
                    throw new InvalidArgumentException('External category ID is empty or exceeds 512 characters.');
                }

                if ($name === '' || mb_strlen($name) > 255) {
                    throw new InvalidArgumentException(sprintf('Category [%s] has an invalid name.', $externalId));
                }

                if (isset($records[$externalId])) {
                    throw new InvalidArgumentException(sprintf('Duplicate external category ID [%s].', $externalId));
                }

                $records[$externalId] = new CategoryImportRecord(
                    externalId: $externalId,
                    parentExternalId: $parentExternalId,
                    name: $name,
                    sortOrder: max(0, $node->sortOrder),
                    depth: $depth,
                );

                $walk($node->children, $externalId, $depth + 1);
            }
        };

        $walk($nodes, null, 0);

        return $records;
    }

    /**
     * @return array<string, mixed>
     */
    private function recordState(
        CategoryImportRecord $record,
        ?EloquentCategory $existing,
    ): array {
        return [
            'categoryId'     => $existing?->id,
            'externalId'     => $record->externalId,
            'parentReference'=> $record->parentExternalId !== null
                ? 'external:' . $record->parentExternalId
                : null,
            'name'           => $record->name,
            'slug'           => $existing?->slug,
            'sortOrder'      => $record->sortOrder,
            'isActive'       => true,
            'depth'          => $record->depth,
        ];
    }

    private function requiredIdentifier(string $value, string $label): string
    {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > 100) {
            throw new InvalidArgumentException(sprintf('Category import %s is empty or too long.', $label));
        }

        return $value;
    }
}
