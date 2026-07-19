<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Catalog\Application\Services\CategoryImporter;
use App\Catalog\Application\Services\PromCategoriesParser;
use App\Catalog\Domain\Enums\CategoryImportStatus;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use RuntimeException;
use Tests\Feature\FeatureTestCase;

class CategoryImportTest extends FeatureTestCase
{
    public function test_import_is_idempotent_and_handles_rename_move_deactivation_and_rollback(): void
    {
        $parser          = $this->app->make(PromCategoriesParser::class);
        $importer        = $this->app->make(CategoryImporter::class);
        $nodesV1         = $parser->parse($this->fixture('prom-categories-v1.html'));

        $previewV1       = $importer->preview(
            nodes: $nodesV1,
            source: 'fixture.catalog',
            sourceVersion: 'fixture-v1',
            sourceUrl: 'fixture://prom-categories-v1.html',
        );

        $this->assertSame([
            'created'     => 5,
            'updated'     => 0,
            'deactivated' => 0,
            'unchanged'   => 0,
        ], $previewV1->stats);
        $this->assertSame(64, strlen($previewV1->checksum));

        $importer->apply($previewV1->id);

        $initialIds      = EloquentCategory::query()
            ->where('external_source', 'fixture.catalog')
            ->pluck('id', 'external_id')
            ->all();

        $this->assertCount(5, $initialIds);

        $repeatedPreview = $importer->preview(
            nodes: $nodesV1,
            source: 'fixture.catalog',
            sourceVersion: 'fixture-v1',
            sourceUrl: 'fixture://prom-categories-v1.html',
        );

        $this->assertSame($previewV1->checksum, $repeatedPreview->checksum);
        $this->assertSame([
            'created'     => 0,
            'updated'     => 0,
            'deactivated' => 0,
            'unchanged'   => 5,
        ], $repeatedPreview->stats);
        $this->assertSame([], $repeatedPreview->diff);

        $importer->apply($repeatedPreview->id);

        $this->assertSame(5, EloquentCategory::query()
            ->where('external_source', 'fixture.catalog')
            ->count());

        $nodesV2         = $parser->parse($this->fixture('prom-categories-v2.html'));
        $previewV2       = $importer->preview(
            nodes: $nodesV2,
            source: 'fixture.catalog',
            sourceVersion: 'fixture-v2',
            sourceUrl: 'fixture://prom-categories-v2.html',
        );

        $this->assertSame([
            'created'     => 0,
            'updated'     => 3,
            'deactivated' => 1,
            'unchanged'   => 1,
        ], $previewV2->stats);
        $this->assertSame(
            ['deactivate' => 1, 'update' => 3],
            collect($previewV2->diff)->pluck('action')->countBy()->sortKeys()->all(),
        );

        $importer->apply($previewV2->id);

        $rootA           = $this->category('fixture.catalog', 'id:root-a');
        $rootB           = $this->category('fixture.catalog', 'id:root-b');
        $group           = $this->category('fixture.catalog', 'id:group-one');
        $leaf            = $this->category('fixture.catalog', 'id:leaf-one');
        $removedLeaf     = $this->category('fixture.catalog', 'id:leaf-removed');

        $this->assertSame($initialIds['id:root-a'], $rootA->id);
        $this->assertSame($initialIds['id:group-one'], $group->id);
        $this->assertSame($initialIds['id:leaf-one'], $leaf->id);
        $this->assertSame('Root Alpha Renamed', $rootA->name);
        $this->assertSame($rootB->id, $group->parent_id);
        $this->assertSame('Leaf One Renamed', $leaf->name);
        $this->assertFalse($removedLeaf->is_active);

        $rolledBack      = $importer->rollback($previewV2->id);

        $this->assertSame(CategoryImportStatus::ROLLED_BACK, $rolledBack->status);
        $this->assertSame('Root Alpha', $this->category('fixture.catalog', 'id:root-a')->name);
        $this->assertSame(
            $initialIds['id:root-a'],
            $this->category('fixture.catalog', 'id:group-one')->parent_id,
        );
        $this->assertSame('Leaf One', $this->category('fixture.catalog', 'id:leaf-one')->name);
        $this->assertTrue($this->category('fixture.catalog', 'id:leaf-removed')->is_active);
    }

    public function test_apply_rejects_a_preview_made_stale_by_another_import(): void
    {
        $parser   = $this->app->make(PromCategoriesParser::class);
        $importer = $this->app->make(CategoryImporter::class);
        $nodes    = $parser->parse($this->fixture('prom-categories-v1.html'));
        $first    = $importer->preview(
            nodes: $nodes,
            source: 'fixture.catalog',
            sourceVersion: 'fixture-v1',
            sourceUrl: 'fixture://prom-categories-v1.html',
        );
        $stale    = $importer->preview(
            nodes: $nodes,
            source: 'fixture.catalog',
            sourceVersion: 'fixture-v1',
            sourceUrl: 'fixture://prom-categories-v1.html',
        );

        $importer->apply($first->id);

        try {
            $importer->apply($stale->id);
            $this->fail('A stale category import preview was applied.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Preview устарел', $exception->getMessage());
        }

        $this->assertSame(CategoryImportStatus::PREVIEW, $stale->fresh()?->status);
        $this->assertSame(5, EloquentCategory::query()
            ->where('external_source', 'fixture.catalog')
            ->count());
    }

    public function test_rollback_deactivates_created_categories_without_deleting_them(): void
    {
        $parser     = $this->app->make(PromCategoriesParser::class);
        $importer   = $this->app->make(CategoryImporter::class);
        $preview    = $importer->preview(
            nodes: $parser->parse($this->fixture('prom-categories-v1.html')),
            source: 'fixture.catalog',
            sourceVersion: 'fixture-v1',
            sourceUrl: 'fixture://prom-categories-v1.html',
        );

        $importer->apply($preview->id);
        $rolledBack = $importer->rollback($preview->id);

        $this->assertSame(CategoryImportStatus::ROLLED_BACK, $rolledBack->status);
        $this->assertSame(5, EloquentCategory::query()
            ->where('external_source', 'fixture.catalog')
            ->count());
        $this->assertSame(0, EloquentCategory::query()
            ->where('external_source', 'fixture.catalog')
            ->where('is_active', true)
            ->count());
    }

    private function fixture(string $name): string
    {
        $contents = file_get_contents(base_path('tests/Fixtures/catalog/' . $name));

        $this->assertIsString($contents);

        return $contents;
    }

    private function category(string $source, string $externalId): EloquentCategory
    {
        return EloquentCategory::query()
            ->where('external_source', $source)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }
}
