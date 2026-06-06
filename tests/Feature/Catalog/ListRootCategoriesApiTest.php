<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Tests\Feature\FeatureTestCase;

class ListRootCategoriesApiTest extends FeatureTestCase
{
    public function test_root_categories_can_be_listed(): void
    {
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
            ]);
    }
}
