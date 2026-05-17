<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Database\Seeders\CatalogDemoSeeder;
use Tests\Feature\FeatureTestCase;

class CatalogDemoSeederTest extends FeatureTestCase
{
    public function test_catalog_demo_seeder_creates_categories_and_attribute_definitions_idempotently(): void
    {
        $this->seed(CatalogDemoSeeder::class);
        $this->seed(CatalogDemoSeeder::class);

        $smartphones = EloquentCategory::query()
            ->where('slug', 'smartfony')
            ->firstOrFail();

        $this->assertDatabaseHas('categories', [
            'slug' => 'smartfony',
            'name' => 'Смартфоны',
        ]);
        $this->assertDatabaseHas('category_attribute_definitions', [
            'category_id' => $smartphones->id,
            'slug'        => 'brend',
            'name'        => 'Бренд',
        ]);
        $this->assertSame(1, EloquentCategory::query()->where('slug', 'smartfony')->count());
        $this->assertSame(
            1,
            EloquentCategoryAttributeDefinition::query()
                ->where('category_id', $smartphones->id)
                ->where('slug', 'brend')
                ->count(),
        );
    }
}
