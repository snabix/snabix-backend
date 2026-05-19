<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\FeatureTestCase;

class CategoryAttributeDefinitionApiTest extends FeatureTestCase
{
    public function test_admin_category_attribute_definition_api_requires_admin_session_guard(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $category           = $categoryRepository->save([
            'name' => 'Двери',
            'slug' => 'dveri',
        ]);
        $user               = EloquentUser::factory()->create();

        $payload            = [
            'categoryId'        => $category->id,
            'name'              => 'Материал',
            'slug'              => 'material',
            'type'              => 4,
            'options'           => ['Дерево', 'Металл'],
            'isRequired'        => true,
            'isFilterable'      => true,
            'isActive'          => true,
            'appliesToChildren' => true,
            'sortOrder'         => 10,
        ];

        $this
            ->postJson('/api/v1/admin/category-attribute-definitions', $payload)
            ->assertUnauthorized();

        $this
            ->actingAs($user)
            ->postJson('/api/v1/admin/category-attribute-definitions', $payload)
            ->assertUnauthorized();

        $this->assertFalse(
            EloquentCategoryAttributeDefinition::query()
                ->where('slug', 'material')
                ->exists(),
        );
    }

    public function test_admin_session_guard_can_manage_category_attribute_definitions_from_spa_request(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $category           = $categoryRepository->save([
            'name' => 'Кресла',
            'slug' => 'kresla',
        ]);
        $admin              = EloquentAdmin::query()->create([
            'name'     => 'SPA Catalog Admin',
            'email'    => 'spa-catalog-admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $payload            = [
            'categoryId'        => $category->id,
            'name'              => 'Цвет',
            'slug'              => 'cvet',
            'type'              => 4,
            'options'           => ['Белый', 'Черный'],
            'isRequired'        => false,
            'isFilterable'      => true,
            'isActive'          => true,
            'appliesToChildren' => true,
            'sortOrder'         => 10,
        ];
        $csrfToken          = 'admin-spa-csrf-token';

        $this->assertSame('session', config('auth.guards.admin.driver'));
        $this->assertSame(ValidateCsrfToken::class, config('sanctum.middleware.validate_csrf_token'));

        $this
            ->withHeader('Origin', 'http://localhost:3000')
            ->withHeader('X-CSRF-TOKEN', $csrfToken)
            ->withSession(['_token' => $csrfToken])
            ->actingAs($admin, 'admin')
            ->postJson('/api/v1/admin/category-attribute-definitions', $payload)
            ->assertOk()
            ->assertJsonPath('data.slug', 'cvet');
    }

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

    public function test_category_attributes_endpoint_returns_form_metadata(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $category           = $categoryRepository->save([
            'name' => 'Ноутбуки',
            'slug' => 'noutbuki-api',
        ]);
        $admin              = EloquentAdmin::query()->create([
            'name'     => 'Catalog Metadata Admin',
            'email'    => 'catalog-metadata-admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $this
            ->actingAs($admin, 'admin')
            ->postJson('/api/v1/admin/category-attribute-definitions', [
                'categoryId'        => $category->id,
                'name'              => 'Производитель',
                'slug'              => 'proizvoditel',
                'type'              => 4,
                'options'           => ['Apple', 'ASUS'],
                'placeholder'       => 'Выберите производителя',
                'helpText'          => 'Укажите бренд устройства.',
                'defaultValue'      => null,
                'groupName'         => 'Основные',
                'isRequired'        => true,
                'isFilterable'      => true,
                'showInCard'        => true,
                'isActive'          => true,
                'appliesToChildren' => true,
                'sortOrder'         => 10,
            ])
            ->assertOk();

        $this
            ->getJson('/api/v1/categories/' . $category->id . '/attributes')
            ->assertOk()
            ->assertJsonPath('data.items.0.placeholder', 'Выберите производителя')
            ->assertJsonPath('data.items.0.helpText', 'Укажите бренд устройства.')
            ->assertJsonPath('data.items.0.groupName', 'Основные')
            ->assertJsonPath('data.items.0.showInCard', true);
    }
}
