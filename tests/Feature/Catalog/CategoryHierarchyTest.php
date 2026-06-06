<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
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
}
