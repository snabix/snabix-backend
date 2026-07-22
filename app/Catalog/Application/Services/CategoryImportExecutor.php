<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Application\Support\CategoryImportChange;
use App\Catalog\Domain\Enums\CategoryImportAction;
use App\Catalog\Domain\Enums\CategoryImportStatus;
use App\Catalog\Infrastructure\Models\EloquentCategoryImportManifest;
use App\Shared\Application\Support\ReferenceDataCache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

readonly class CategoryImportExecutor
{
    public function __construct(
        private CategoryImportStateService $stateService,
        private ReferenceDataCache $cache,
    ) {}

    public function apply(string $manifestId): EloquentCategoryImportManifest
    {
        return $this->cache->batchCatalogInvalidation(fn(): EloquentCategoryImportManifest => DB::transaction(function () use ($manifestId): EloquentCategoryImportManifest {
            $manifest = $this->lockedManifest($manifestId);

            if ($manifest->status !== CategoryImportStatus::PREVIEW) {
                throw new RuntimeException('Применить можно только manifest в статусе preview.');
            }

            $changes  = $this->changes($manifest);

            $this->assertDatabaseMatches($manifest->source, $changes, useAfterState: false);
            $this->applyChanges($manifest->source, $changes);
            $this->stateService->markRecordsAsSeen($manifest);

            $manifest->forceFill([
                'status'     => CategoryImportStatus::APPLIED,
                'applied_at' => now(),
            ])->save();

            return $manifest->fresh() ?? $manifest;
        }));
    }

    public function rollback(string $manifestId): EloquentCategoryImportManifest
    {
        return $this->cache->batchCatalogInvalidation(fn(): EloquentCategoryImportManifest => DB::transaction(function () use ($manifestId): EloquentCategoryImportManifest {
            $manifest = $this->lockedManifest($manifestId);

            if ($manifest->status !== CategoryImportStatus::APPLIED) {
                throw new RuntimeException('Откатить можно только примененный category import manifest.');
            }

            $changes  = $this->changes($manifest);

            $this->assertDatabaseMatches($manifest->source, $changes, useAfterState: true);
            $this->restoreChanges($manifest->source, $changes);

            $manifest->forceFill([
                'status'         => CategoryImportStatus::ROLLED_BACK,
                'rolled_back_at' => now(),
            ])->save();

            return $manifest->fresh() ?? $manifest;
        }));
    }

    private function lockedManifest(string $manifestId): EloquentCategoryImportManifest
    {
        return EloquentCategoryImportManifest::query()
            ->whereKey($manifestId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * @return array<int, CategoryImportChange>
     */
    private function changes(EloquentCategoryImportManifest $manifest): array
    {
        $changes = [];

        foreach ($manifest->diff as $payload) {
            $changes[] = CategoryImportChange::fromArray($payload);
        }

        return $changes;
    }

    /**
     * @param array<int, CategoryImportChange> $changes
     */
    private function assertDatabaseMatches(
        string $source,
        array $changes,
        bool $useAfterState,
    ): void {
        foreach ($changes as $change) {
            $category = $this->stateService->find($source, $change->externalId);
            $expected = $useAfterState ? $change->after : $change->before;

            if (! $useAfterState && $change->action === CategoryImportAction::CREATE) {
                if ($category !== null) {
                    throw new RuntimeException(sprintf(
                        'Preview устарел: external ID [%s] уже существует.',
                        $change->externalId,
                    ));
                }

                continue;
            }

            if (
                $category === null
                || $expected === null
                || ! $this->stateService->statesMatch($this->stateService->categoryState($category), $expected)
            ) {
                throw new RuntimeException(sprintf(
                    'Manifest устарел: категория [%s] изменилась после preview/apply.',
                    $change->externalId,
                ));
            }
        }
    }

    /**
     * @param array<int, CategoryImportChange> $changes
     */
    private function applyChanges(string $source, array $changes): void
    {
        $upserts       = array_values(array_filter(
            $changes,
            static fn(CategoryImportChange $change): bool => $change->action !== CategoryImportAction::DEACTIVATE,
        ));
        $deactivations = array_values(array_filter(
            $changes,
            static fn(CategoryImportChange $change): bool => $change->action === CategoryImportAction::DEACTIVATE,
        ));

        usort($upserts, static fn(CategoryImportChange $left, CategoryImportChange $right): int => $left->depth <=> $right->depth);
        usort($deactivations, static fn(CategoryImportChange $left, CategoryImportChange $right): int => $right->depth <=> $left->depth);

        foreach ($upserts as $change) {
            $this->stateService->persist(
                source: $source,
                externalId: $change->externalId,
                state: $change->after,
            );
        }

        foreach ($deactivations as $change) {
            $category = $this->stateService->required($source, $change->externalId);

            $category->forceFill([
                'is_active' => false,
            ])->save();
        }
    }

    /**
     * @param array<int, CategoryImportChange> $changes
     */
    private function restoreChanges(string $source, array $changes): void
    {
        $restores = array_values(array_filter(
            $changes,
            static fn(CategoryImportChange $change): bool => $change->action !== CategoryImportAction::CREATE,
        ));
        $created  = array_values(array_filter(
            $changes,
            static fn(CategoryImportChange $change): bool => $change->action === CategoryImportAction::CREATE,
        ));

        usort(
            $restores,
            fn(CategoryImportChange $left, CategoryImportChange $right): int => $this->stateDepth($left->before)
                <=> $this->stateDepth($right->before),
        );
        usort($created, static fn(CategoryImportChange $left, CategoryImportChange $right): int => $right->depth <=> $left->depth);

        foreach ($restores as $change) {
            if ($change->before === null) {
                throw new RuntimeException('Rollback manifest does not contain the previous category state.');
            }

            $this->stateService->persist(
                source: $source,
                externalId: $change->externalId,
                state: $change->before,
            );
        }

        foreach ($created as $change) {
            $category = $this->stateService->required($source, $change->externalId);

            $category->forceFill([
                'is_active' => false,
            ])->save();
        }
    }

    /**
     * @param array<string, mixed>|null $state
     */
    private function stateDepth(?array $state): int
    {
        $depth = $state['depth'] ?? 0;

        return is_int($depth) ? $depth : 0;
    }
}
