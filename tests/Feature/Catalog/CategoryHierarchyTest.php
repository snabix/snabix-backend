<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Validation\ValidationException;
use Tests\Feature\FeatureTestCase;

class CategoryHierarchyTest extends FeatureTestCase
{
    public function test_it_builds_depth_and_path_for_nested_categories(): void
    {
        $root       = EloquentCategory::query()->create([
            'name' => 'Электроника',
        ]);

        $child      = EloquentCategory::query()->create([
            'name'      => 'Смартфоны',
            'parent_id' => $root->id,
        ]);

        $grandChild = EloquentCategory::query()->create([
            'name'      => 'Android',
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
        $root            = EloquentCategory::query()->create([
            'name' => 'Транспорт',
        ]);

        $child           = EloquentCategory::query()->create([
            'name'      => 'Автомобили',
            'parent_id' => $root->id,
        ]);

        $root->parent_id = $child->id;

        $this->expectException(ValidationException::class);

        $root->save();
    }
}
