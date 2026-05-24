<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
use Illuminate\Validation\ValidationException;
use Tests\Feature\FeatureTestCase;

class CategoryAttributeDefinitionApiTest extends FeatureTestCase
{
    public function test_admin_category_attribute_definition_http_api_is_not_exposed(): void
    {
        $this
            ->getJson('/api/v1/admin/category-attribute-definitions')
            ->assertNotFound();
    }

    public function test_category_attributes_endpoint_returns_form_metadata(): void
    {
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $category                      = $categoryRepository->save([
            'name' => 'Ноутбуки',
            'slug' => 'noutbuki-api',
        ]);

        $attributeDefinitionRepository->save([
            'category_id'         => $category->id,
            'name'                => 'Производитель',
            'slug'                => 'proizvoditel',
            'type'                => 4,
            'options'             => ['Apple', 'ASUS'],
            'placeholder'         => 'Выберите производителя',
            'help_text'           => 'Укажите бренд устройства.',
            'default_value'       => null,
            'group_name'          => 'Основные',
            'is_required'         => true,
            'is_filterable'       => true,
            'show_in_card'        => true,
            'is_active'           => true,
            'applies_to_children' => true,
            'sort_order'          => 10,
        ]);

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
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $category                      = $categoryRepository->save([
            'name' => 'Планшеты',
            'slug' => 'planshety-api',
        ]);

        $attribute                     = $attributeDefinitionRepository->save([
            'category_id'      => $category->id,
            'name'             => 'Модель',
            'slug'             => 'model',
            'type'             => 1,
            'dependency_rules' => [
                [
                    'attributeSlug' => 'brand',
                    'operator'      => 'equals',
                    'value'         => 'Apple',
                ],
            ],
            'is_required'      => true,
            'is_filterable'    => true,
            'show_in_card'     => true,
        ]);

        $this
            ->getJson('/api/v1/categories/' . $category->id . '/attributes')
            ->assertOk()
            ->assertJsonFragment([
                'attributeSlug' => 'brand',
                'operator'      => 'equals',
                'value'         => 'Apple',
            ])
            ->assertJsonFragment([
                'schemaVersion' => 1,
                'slug'          => 'model',
            ]);

        $updatedAttribute              = $attributeDefinitionRepository->save([
            'category_id'      => $category->id,
            'name'             => 'Модель',
            'slug'             => 'model',
            'type'             => 4,
            'options'          => ['iPad Air', 'iPad Pro'],
            'dependency_rules' => [
                [
                    'attributeSlug' => 'brand',
                    'operator'      => 'equals',
                    'value'         => 'Apple',
                ],
            ],
            'is_required'      => true,
            'is_filterable'    => true,
            'show_in_card'     => true,
        ], (int) $attribute->id);

        $this->assertSame(2, $updatedAttribute->schema_version);
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

    public function test_category_attribute_definition_repository_blocks_delete_with_listing_values(): void
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

        try {
            $attributeDefinitionRepository->delete($attribute);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('attributeDefinitionId', $exception->errors());
            $this->assertDatabaseHas('category_attribute_definitions', [
                'id' => $attribute->id,
            ]);

            return;
        }

        $this->fail('Category attribute definition with listing values was deleted.');
    }
}
