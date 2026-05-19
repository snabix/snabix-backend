<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
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

    public function test_category_attributes_support_dependency_rules_and_schema_version(): void
    {
        $categoryRepository    = app(CategoryRepositoryInterface::class);
        $category              = $categoryRepository->save([
            'name' => 'Планшеты',
            'slug' => 'planshety-api',
        ]);
        $admin                 = EloquentAdmin::query()->create([
            'name'     => 'Catalog Dependency Admin',
            'email'    => 'catalog-dependency-admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $createResponse        = $this
            ->actingAs($admin, 'admin')
            ->postJson('/api/v1/admin/category-attribute-definitions', [
                'categoryId'       => $category->id,
                'name'             => 'Модель',
                'slug'             => 'model',
                'type'             => 1,
                'dependencyRules'  => [
                    [
                        'attributeSlug' => 'brand',
                        'operator'      => 'equals',
                        'value'         => 'Apple',
                    ],
                ],
                'isRequired'       => true,
                'isFilterable'     => true,
                'showInCard'       => true,
            ]);

        $createResponse
            ->assertOk()
            ->assertJsonPath('data.dependencyRules.0.attributeSlug', 'brand')
            ->assertJsonPath('data.dependencyRules.0.operator', 'equals')
            ->assertJsonPath('data.schemaVersion', 1);

        $attributeDefinitionId = $createResponse->json('data.id');

        $this->assertIsInt($attributeDefinitionId);

        $this
            ->actingAs($admin, 'admin')
            ->patchJson('/api/v1/admin/category-attribute-definitions/' . $attributeDefinitionId, [
                'categoryId'       => $category->id,
                'name'             => 'Модель',
                'slug'             => 'model',
                'type'             => 4,
                'options'          => ['iPad Air', 'iPad Pro'],
                'dependencyRules'  => [
                    [
                        'attributeSlug' => 'brand',
                        'operator'      => 'equals',
                        'value'         => 'Apple',
                    ],
                ],
                'isRequired'       => true,
                'isFilterable'     => true,
                'showInCard'       => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.schemaVersion', 2);
    }

    public function test_listing_attribute_values_keep_attribute_schema_snapshot(): void
    {
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $category                      = $categoryRepository->save([
            'name' => 'Телевизоры',
            'slug' => 'televizory-api',
        ]);
        $attribute                     = $attributeDefinitionRepository->save([
            'category_id'         => $category->id,
            'name'                => 'Диагональ',
            'slug'                => 'diagonal',
            'type'                => 4,
            'options'             => ['43', '55'],
            'is_required'         => true,
            'show_in_card'        => true,
            'is_active'           => true,
            'applies_to_children' => true,
        ]);
        $user                          = EloquentUser::factory()->create();

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings', [
                'categoryId'      => $category->id,
                'type'            => 1,
                'condition'       => 2,
                'title'           => 'Телевизор 55 дюймов',
                'description'     => 'Хорошее состояние, яркая картинка.',
                'price'           => 45000,
                'currency'        => 'RUB',
                'attributeValues' => [
                    $attribute->id => '55',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.attributeValues.0.schemaVersion', 1)
            ->assertJsonPath('data.attributeValues.0.name', 'Диагональ');

        $storedValue                   = EloquentListingAttributeValue::query()
            ->where('attribute_definition_id', $attribute->id)
            ->firstOrFail();

        $this->assertSame(1, $storedValue->attribute_schema_version);
        $this->assertSame('Диагональ', $storedValue->attribute_snapshot['name'] ?? null);
    }

    public function test_admin_cannot_delete_category_attribute_definition_with_listing_values(): void
    {
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $category                      = $categoryRepository->save([
            'name' => 'Кофемашины',
            'slug' => 'kofemashiny-api',
        ]);
        $attribute                     = $attributeDefinitionRepository->save([
            'category_id'         => $category->id,
            'name'                => 'Тип',
            'slug'                => 'tip',
            'type'                => 4,
            'options'             => ['Автоматическая', 'Капсульная'],
            'is_required'         => true,
            'is_active'           => true,
            'applies_to_children' => true,
        ]);
        $user                          = EloquentUser::factory()->create();
        $admin                         = EloquentAdmin::query()->create([
            'name'     => 'Catalog Delete Guard Admin',
            'email'    => 'catalog-delete-guard-admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings', [
                'categoryId'      => $category->id,
                'type'            => 1,
                'condition'       => 2,
                'title'           => 'Кофемашина',
                'description'     => 'Рабочая кофемашина для дома.',
                'price'           => 12000,
                'currency'        => 'RUB',
                'attributeValues' => [
                    $attribute->id => 'Автоматическая',
                ],
            ])
            ->assertCreated();

        $this
            ->actingAs($admin, 'admin')
            ->deleteJson('/api/v1/admin/category-attribute-definitions/' . $attribute->id)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['attributeDefinitionId']);

        $this->assertDatabaseHas('category_attribute_definitions', [
            'id' => $attribute->id,
        ]);
    }

    public function test_admin_can_bulk_export_and_import_category_attribute_definitions(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $category           = $categoryRepository->save([
            'name' => 'Проекторы',
            'slug' => 'proektory-api',
        ]);
        $admin              = EloquentAdmin::query()->create([
            'name'     => 'Catalog Import Export Admin',
            'email'    => 'catalog-import-export-admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $this
            ->actingAs($admin, 'admin')
            ->postJson('/api/v1/admin/category-attribute-definitions/import', [
                'items' => [
                    [
                        'categoryId'   => $category->id,
                        'name'         => 'Яркость',
                        'slug'         => 'yarkost',
                        'type'         => 2,
                        'unit'         => 'лм',
                        'isFilterable' => true,
                        'showInCard'   => true,
                    ],
                    [
                        'categoryId' => $category->id,
                        'name'       => 'Технология',
                        'slug'       => 'tehnologiya',
                        'type'       => 4,
                        'options'    => ['DLP', 'LCD'],
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.created', 2)
            ->assertJsonPath('data.updated', 0);

        $this
            ->actingAs($admin, 'admin')
            ->postJson('/api/v1/admin/category-attribute-definitions/import', [
                'items' => [
                    [
                        'categoryId'   => $category->id,
                        'name'         => 'Яркость ANSI',
                        'slug'         => 'yarkost',
                        'type'         => 2,
                        'unit'         => 'ANSI лм',
                        'isFilterable' => true,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.created', 0)
            ->assertJsonPath('data.updated', 1)
            ->assertJsonPath('data.items.0.name', 'Яркость ANSI');

        $this
            ->actingAs($admin, 'admin')
            ->getJson('/api/v1/admin/category-attribute-definitions/export?onlyActive=1')
            ->assertOk()
            ->assertJsonPath('data.meta.schemaVersion', 1)
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonPath('data.items.0.category.id', $category->id);
    }
}
