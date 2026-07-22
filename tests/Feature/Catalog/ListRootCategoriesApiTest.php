<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature\FeatureTestCase;

class ListRootCategoriesApiTest extends FeatureTestCase
{
    public function test_root_categories_can_be_listed(): void
    {
        Cache::flush();

        $repository = app(CategoryRepositoryInterface::class);

        $repository->save([
            'name' => 'Электроника',
            'slug' => 'elektronika',
        ]);

        $this
            ->getJson('/api/v1/categories/list')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Электроника')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'catalogKind',
                        'catalogKindLabel',
                        // Legacy aliases stay available through the documented deprecation window.
                        'catalogType',
                        'catalogTypeLabel',
                        'parentId',
                        'name',
                        'slug',
                        'description',
                        'icon',
                        'sortOrder',
                        'isActive',
                        'path',
                        'depth',
                        'children',
                    ],
                ],
            ])
            ->assertJsonPath('data.0.catalogKind', 'product');
    }

    public function test_root_categories_are_cached_and_invalidated_after_changes(): void
    {
        Cache::flush();

        $repository = app(CategoryRepositoryInterface::class);
        $category   = $repository->save([
            'name' => 'Материалы',
            'slug' => 'materialy',
        ]);

        $this
            ->getJson('/api/v1/categories/list')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Материалы');

        $queries    = [];
        DB::listen(static function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this
            ->getJson('/api/v1/categories/list')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Материалы');

        $this->assertFalse(collect($queries)->contains(
            static fn(string $sql): bool => str_contains($sql, 'from "categories"'),
        ));

        $repository->save([
            'name' => 'Стройматериалы',
            'slug' => 'strojmaterialy',
        ], $category->id);

        $this
            ->getJson('/api/v1/categories/list')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Стройматериалы');
    }
}
