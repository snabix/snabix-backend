<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\FeatureTestCase;

class CategoryAttributeDefinitionApiTest extends FeatureTestCase
{
    public function test_admin_can_manage_category_attribute_definitions_via_api(): void
    {
        $categoryRepository    = app(CategoryRepositoryInterface::class);
        $category              = $categoryRepository->save([
            'name' => 'Шкафы',
            'slug' => 'shkafy',
        ]);
        $admin                 = EloquentAdmin::query()->create([
            'name'     => 'Catalog Admin',
            'email'    => 'catalog-admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $createResponse        = $this
            ->actingAs($admin, 'admin')
            ->postJson('/api/v1/admin/category-attribute-definitions', [
                'categoryId'        => $category->id,
                'name'              => 'Материал',
                'slug'              => 'material',
                'type'              => 4,
                'options'           => ['Дуб', 'Бук', 'МДФ'],
                'isRequired'        => true,
                'isFilterable'      => true,
                'isActive'          => true,
                'appliesToChildren' => true,
                'sortOrder'         => 10,
            ]);

        $createResponse
            ->assertOk()
            ->assertJsonPath('data.name', 'Материал')
            ->assertJsonPath('data.type', 4)
            ->assertJsonPath('data.category.id', $category->id);

        $attributeDefinitionId = $createResponse->json('data.id');

        $this->assertIsInt($attributeDefinitionId);

        $this
            ->actingAs($admin, 'admin')
            ->getJson('/api/v1/admin/category-attribute-definitions')
            ->assertOk()
            ->assertJsonPath('data.0.id', $attributeDefinitionId);

        $this
            ->actingAs($admin, 'admin')
            ->getJson('/api/v1/admin/category-attribute-definitions/' . $attributeDefinitionId)
            ->assertOk()
            ->assertJsonPath('data.slug', 'material');

        $this
            ->actingAs($admin, 'admin')
            ->patchJson('/api/v1/admin/category-attribute-definitions/' . $attributeDefinitionId, [
                'categoryId'        => $category->id,
                'name'              => 'Основной материал',
                'slug'              => 'osnovnoj-material',
                'type'              => 4,
                'options'           => ['Дуб', 'Бук', 'МДФ'],
                'isRequired'        => true,
                'isFilterable'      => true,
                'isActive'          => true,
                'appliesToChildren' => true,
                'sortOrder'         => 20,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Основной материал')
            ->assertJsonPath('data.sortOrder', 20);

        $this
            ->actingAs($admin, 'admin')
            ->deleteJson('/api/v1/admin/category-attribute-definitions/' . $attributeDefinitionId)
            ->assertOk()
            ->assertJsonPath('data.deleted', true);
    }
}
