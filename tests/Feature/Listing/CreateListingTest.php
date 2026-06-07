<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Domain\Enums\ListingStatus;
use Tests\Feature\FeatureTestCase;

class CreateListingTest extends FeatureTestCase
{
    public function test_user_can_create_listing_with_prepared_category_attributes(): void
    {
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $user                          = EloquentUser::factory()->create();
        $category                      = $categoryRepository->save([
            'name'         => 'Запчасти',
            'slug'         => 'zapchasti',
            'catalog_type' => 1,
        ]);
        $brandAttribute                = $attributeDefinitionRepository->save([
            'category_id'         => $category->id,
            'name'                => 'Бренд',
            'slug'                => 'brend',
            'type'                => 4,
            'options'             => ['Bosch', 'Makita'],
            'is_required'         => true,
            'is_filterable'       => true,
            'is_active'           => true,
            'applies_to_children' => true,
            'sort_order'          => 10,
        ]);
        $isOriginalAttribute           = $attributeDefinitionRepository->save([
            'category_id'         => $category->id,
            'name'                => 'Оригинальная деталь',
            'slug'                => 'original',
            'type'                => 3,
            'is_required'         => false,
            'is_filterable'       => true,
            'is_active'           => true,
            'applies_to_children' => true,
            'sort_order'          => 20,
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings', [
                'categoryId'      => $category->id,
                'type'            => 1,
                'condition'       => 2,
                'title'           => 'Редуктор Bosch',
                'description'     => 'Оригинальная запасная часть в хорошем состоянии.',
                'price'           => 14500,
                'currency'        => 'rub',
                'isNegotiable'    => true,
                'attributeValues' => [
                    $brandAttribute->id      => 'Bosch',
                    $isOriginalAttribute->id => true,
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Редуктор Bosch')
            ->assertJsonPath('data.status', ListingStatus::PENDING_REVIEW->value)
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.attributeValues.0.displayValue', 'Bosch')
            ->assertJsonPath('data.attributeValues.1.displayValue', 'Да');

        $this->assertDatabaseHas('system_logs', [
            'category' => 'listing',
            'action'   => 'listing.create',
            'user_id'  => $user->id,
        ]);
    }

    public function test_user_cannot_set_moderation_fields_when_creating_listing(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Мониторы',
            'slug'         => 'monitory',
            'catalog_type' => 1,
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings', [
                'categoryId'      => $category->id,
                'type'            => 1,
                'status'          => ListingStatus::PUBLISHED->value,
                'condition'       => 2,
                'title'           => 'Игровой монитор',
                'description'     => 'Монитор в хорошем состоянии.',
                'price'           => 25000,
                'currency'        => 'RUB',
                'isFeatured'      => true,
                'rejectionReason' => 'Не должно сохраниться',
                'attributeValues' => [],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', ListingStatus::PENDING_REVIEW->value)
            ->assertJsonPath('data.isFeatured', false)
            ->assertJsonPath('data.rejectionReason', null);
    }

    public function test_user_can_save_listing_as_draft_without_required_category_attributes(): void
    {
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $user                          = EloquentUser::factory()->create();
        $category                      = $categoryRepository->save([
            'name'         => 'Ноутбуки',
            'slug'         => 'noutbuki',
            'catalog_type' => 1,
        ]);

        $attributeDefinitionRepository->save([
            'category_id'         => $category->id,
            'name'                => 'Производитель',
            'slug'                => 'proizvoditel',
            'type'                => 1,
            'is_required'         => true,
            'is_filterable'       => true,
            'is_active'           => true,
            'applies_to_children' => true,
            'sort_order'          => 10,
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings', [
                'categoryId'      => $category->id,
                'type'            => 1,
                'condition'       => 2,
                'title'           => 'Ноутбук для учебы',
                'description'     => 'Описание будет дополнено позже.',
                'price'           => 30000,
                'currency'        => 'RUB',
                'saveAsDraft'     => true,
                'attributeValues' => [],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', ListingStatus::DRAFT->value)
            ->assertJsonPath('data.attributeValues', []);
    }

    public function test_required_category_attribute_must_be_present(): void
    {
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $user                          = EloquentUser::factory()->create();
        $category                      = $categoryRepository->save([
            'name'         => 'Принтеры',
            'slug'         => 'printery',
            'catalog_type' => 1,
        ]);

        $attribute                     = $attributeDefinitionRepository->save([
            'category_id'         => $category->id,
            'name'                => 'Производитель',
            'slug'                => 'proizvoditel',
            'type'                => 1,
            'is_required'         => true,
            'is_filterable'       => true,
            'is_active'           => true,
            'applies_to_children' => true,
            'sort_order'          => 10,
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings', [
                'categoryId'      => $category->id,
                'type'            => 1,
                'condition'       => 2,
                'title'           => 'Лазерный принтер',
                'description'     => 'Рабочий офисный принтер.',
                'price'           => 12000,
                'currency'        => 'RUB',
                'attributeValues' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['attributeValues.' . $attribute->id]);
    }
}
