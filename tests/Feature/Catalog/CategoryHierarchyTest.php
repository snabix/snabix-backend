<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Feature\FeatureTestCase;

class CategoryHierarchyTest extends FeatureTestCase
{
    public function test_it_builds_depth_and_path_for_nested_categories(): void
    {
        $repository = app(CategoryRepositoryInterface::class);

        $root       = $repository->save([
            'name' => 'Электроника',
            'slug' => 'elektronika',
        ]);

        $child      = $repository->save([
            'name'      => 'Смартфоны',
            'slug'      => 'smartfony',
            'parent_id' => $root->id,
        ]);

        $grandChild = $repository->save([
            'name'      => 'Android',
            'slug'      => 'android',
            'parent_id' => $child->id,
        ]);

        $root       = $root->fresh();
        $child      = $child->fresh();
        $grandChild = $grandChild->fresh();

        $this->assertInstanceOf(EloquentCategory::class, $root);
        $this->assertInstanceOf(EloquentCategory::class, $child);
        $this->assertInstanceOf(EloquentCategory::class, $grandChild);

        $this->assertSame(0, $root->depth);
        $this->assertSame('elektronika', $root->path);

        $this->assertSame(1, $child->depth);
        $this->assertSame('elektronika/smartfony', $child->path);

        $this->assertSame(2, $grandChild->depth);
        $this->assertSame('elektronika/smartfony/android', $grandChild->path);
    }

    public function test_it_rejects_circular_parenting(): void
    {
        $repository      = app(CategoryRepositoryInterface::class);

        $root            = $repository->save([
            'name' => 'Транспорт',
            'slug' => 'transport',
        ]);

        $child           = $repository->save([
            'name'      => 'Автомобили',
            'slug'      => 'avtomobili',
            'parent_id' => $root->id,
        ]);

        $this->expectException(ValidationException::class);

        $repository->save([
            'name'      => $root->name,
            'slug'      => $root->slug,
            'parent_id' => $child->id,
        ], $root->id);
    }

    public function test_slug_change_updates_deep_descendants_with_one_invalidation_and_bounded_queries(): void
    {
        $repository  = app(CategoryRepositoryInterface::class);
        $root        = $repository->save([
            'name' => 'Старый корень',
            'slug' => 'old-hierarchy-root',
        ]);
        $parent      = $root;

        for ($depth = 1; $depth <= 8; $depth++) {
            $parent = $repository->save([
                'name'      => 'Узел ' . $depth,
                'slug'      => 'hierarchy-node-' . $depth,
                'parent_id' => $parent->id,
            ]);
        }

        Cache::flush();
        Cache::forever('reference-data:catalog:version', 1);
        $queryCount  = 0;

        DB::listen(static function ($query) use (&$queryCount): void {
            if (str_contains($query->sql, '"categories"')) {
                $queryCount++;
            }
        });

        $repository->save([
            'name' => 'Новый корень',
            'slug' => 'new-hierarchy-root',
        ], $root->id);

        $this->assertTrue(in_array(Cache::get('reference-data:catalog:version'), [2, '2'], true));
        $this->assertLessThanOrEqual(8, $queryCount);
        $freshParent = $parent->fresh();

        $this->assertInstanceOf(EloquentCategory::class, $freshParent);
        $this->assertSame(
            'new-hierarchy-root/hierarchy-node-1/hierarchy-node-2/hierarchy-node-3/'
            . 'hierarchy-node-4/hierarchy-node-5/hierarchy-node-6/hierarchy-node-7/hierarchy-node-8',
            $freshParent->path,
        );
        $this->assertSame(8, $freshParent->depth);
    }
}
