<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature\FeatureTestCase;

class ShowCategoryBranchApiTest extends FeatureTestCase
{
    public function test_branch_supports_arbitrary_depth_with_constant_query_budget(): void
    {
        $repository = app(CategoryRepositoryInterface::class);
        $root       = $repository->save([
            'name' => 'Уровень 0',
            'slug' => 'branch-level-0',
        ]);
        $parent     = $root;

        for ($depth = 1; $depth <= 6; $depth++) {
            $parent = $repository->save([
                'name'      => 'Уровень ' . $depth,
                'slug'      => 'branch-level-' . $depth,
                'parent_id' => $parent->id,
            ]);
        }

        Cache::flush();
        $queryCount = 0;

        DB::listen(static function ($query) use (&$queryCount): void {
            if (str_contains($query->sql, '"categories"') || str_contains($query->sql, '"media"')) {
                $queryCount++;
            }
        });

        $this
            ->getJson('/api/v1/categories/' . $root->id . '/branch')
            ->assertOk()
            ->assertJsonPath(
                'data.children.0.children.0.children.0.children.0.children.0.children.0.name',
                'Уровень 6',
            );

        $this->assertLessThanOrEqual(4, $queryCount);
    }

    public function test_only_active_branch_excludes_inactive_subtrees_and_rejects_inactive_ancestors(): void
    {
        $repository   = app(CategoryRepositoryInterface::class);
        $root         = $repository->save([
            'name' => 'Активный корень',
            'slug' => 'active-root-edge',
        ]);
        $inactive     = $repository->save([
            'name'      => 'Выключенный раздел',
            'slug'      => 'inactive-parent-edge',
            'parent_id' => $root->id,
            'is_active' => false,
        ]);
        $activeChild  = $repository->save([
            'name'      => 'Активный потомок',
            'slug'      => 'active-child-edge',
            'parent_id' => $inactive->id,
        ]);
        $inactiveRoot = $repository->save([
            'name'      => 'Выключенный корень',
            'slug'      => 'inactive-root-edge',
            'is_active' => false,
        ]);
        $repository->save([
            'name'      => 'Потомок выключенного корня',
            'slug'      => 'inactive-root-child-edge',
            'parent_id' => $inactiveRoot->id,
        ]);

        Cache::flush();

        $this
            ->getJson('/api/v1/categories/' . $root->id . '/branch')
            ->assertOk()
            ->assertJsonCount(0, 'data.children')
            ->assertJsonMissing(['id' => $activeChild->id]);

        $this
            ->getJson('/api/v1/categories/' . $activeChild->id . '/branch')
            ->assertNotFound();

        $this
            ->getJson('/api/v1/categories/' . $inactiveRoot->id . '/branch')
            ->assertNotFound();

        $this
            ->getJson('/api/v1/categories/list')
            ->assertOk()
            ->assertJsonMissing(['id' => $inactiveRoot->id]);

        $this->assertTrue(EloquentCategory::query()->whereKey($activeChild->id)->exists());
    }
}
