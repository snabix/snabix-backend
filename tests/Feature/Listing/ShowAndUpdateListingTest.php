<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use Tests\Feature\FeatureTestCase;

class ShowAndUpdateListingTest extends FeatureTestCase
{
    public function test_user_can_show_and_update_own_listing(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Ноутбуки',
            'slug'         => 'noutbuki',
            'catalog_type' => 1,
        ]);
        $listing            = EloquentListing::query()->create([
            'user_id'       => $user->id,
            'category_id'   => $category->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::DRAFT,
            'condition'     => ListingCondition::USED,
            'title'         => 'MacBook Pro 14',
            'slug'          => 'macbook-pro-14',
            'description'   => 'Рабочий ноутбук в хорошем состоянии.',
            'price'         => 170000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
        ]);

        $this
            ->actingAs($user)
            ->getJson('/api/v1/listings/' . $listing->id)
            ->assertOk()
            ->assertJsonPath('data.id', $listing->id)
            ->assertJsonPath('data.title', 'MacBook Pro 14');

        $this
            ->actingAs($user)
            ->patchJson('/api/v1/listings/' . $listing->id, [
                'categoryId'      => $category->id,
                'type'            => ListingType::PRODUCT->value,
                'condition'       => ListingCondition::USED->value,
                'title'           => 'MacBook Pro 14 M3',
                'description'     => 'Обновленное описание объявления.',
                'price'           => 185000,
                'currency'        => 'rub',
                'isNegotiable'    => false,
                'attributeValues' => [],
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'MacBook Pro 14 M3')
            ->assertJsonPath('data.price', 185000)
            ->assertJsonPath('data.currency', 'RUB')
            ->assertJsonPath('data.isNegotiable', false);

        $this->assertDatabaseHas('listings', [
            'id'            => $listing->id,
            'title'         => 'MacBook Pro 14 M3',
            'price'         => 185000,
            'currency'      => 'RUB',
            'is_negotiable' => false,
        ]);
    }

    public function test_user_update_does_not_change_moderation_fields(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Планшеты',
            'slug'         => 'planshety',
            'catalog_type' => 1,
        ]);
        $listing            = EloquentListing::query()->create([
            'user_id'       => $user->id,
            'category_id'   => $category->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::DRAFT,
            'condition'     => ListingCondition::USED,
            'title'         => 'iPad',
            'slug'          => 'ipad',
            'description'   => 'Планшет в рабочем состоянии.',
            'price'         => 45000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
            'is_featured'   => false,
        ]);

        $this
            ->actingAs($user)
            ->patchJson('/api/v1/listings/' . $listing->id, [
                'categoryId'      => $category->id,
                'type'            => ListingType::PRODUCT->value,
                'status'          => ListingStatus::PUBLISHED->value,
                'condition'       => ListingCondition::USED->value,
                'title'           => 'iPad Air',
                'description'     => 'Описание обновлено.',
                'price'           => 50000,
                'currency'        => 'RUB',
                'isFeatured'      => true,
                'rejectionReason' => 'Не должно сохраниться',
                'attributeValues' => [],
            ])
            ->assertOk()
            ->assertJsonPath('data.status', ListingStatus::DRAFT->value)
            ->assertJsonPath('data.isFeatured', false)
            ->assertJsonPath('data.rejectionReason', null);
    }

    public function test_update_removes_cleared_or_no_longer_applicable_attribute_values(): void
    {
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $user                          = EloquentUser::factory()->create();
        $firstCategory                 = $categoryRepository->save([
            'name'         => 'Кресла',
            'slug'         => 'kresla',
            'catalog_type' => 1,
        ]);
        $secondCategory                = $categoryRepository->save([
            'name'         => 'Столы',
            'slug'         => 'stoly',
            'catalog_type' => 1,
        ]);
        $colorAttribute                = $attributeDefinitionRepository->save([
            'category_id'         => $firstCategory->id,
            'name'                => 'Цвет',
            'slug'                => 'cvet',
            'type'                => 1,
            'is_required'         => false,
            'is_filterable'       => true,
            'is_active'           => true,
            'applies_to_children' => true,
            'sort_order'          => 10,
        ]);
        $materialAttribute             = $attributeDefinitionRepository->save([
            'category_id'         => $secondCategory->id,
            'name'                => 'Материал',
            'slug'                => 'material',
            'type'                => 1,
            'is_required'         => false,
            'is_filterable'       => true,
            'is_active'           => true,
            'applies_to_children' => true,
            'sort_order'          => 10,
        ]);
        $listing                       = EloquentListing::query()->create([
            'user_id'       => $user->id,
            'category_id'   => $firstCategory->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::DRAFT,
            'condition'     => ListingCondition::USED,
            'title'         => 'Офисное кресло',
            'slug'          => 'ofisnoe-kreslo',
            'description'   => 'Удобное офисное кресло.',
            'price'         => 9000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
        ]);

        $this
            ->actingAs($user)
            ->patchJson('/api/v1/listings/' . $listing->id, [
                'categoryId'      => $firstCategory->id,
                'type'            => ListingType::PRODUCT->value,
                'condition'       => ListingCondition::USED->value,
                'title'           => 'Офисное кресло',
                'description'     => 'Удобное офисное кресло.',
                'price'           => 9000,
                'currency'        => 'RUB',
                'isNegotiable'    => true,
                'attributeValues' => [
                    $colorAttribute->id => 'Черный',
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('listing_attribute_values', [
            'listing_id'              => $listing->id,
            'attribute_definition_id' => $colorAttribute->id,
            'display_value'           => 'Черный',
        ]);

        $this
            ->actingAs($user)
            ->patchJson('/api/v1/listings/' . $listing->id, [
                'categoryId'      => $secondCategory->id,
                'type'            => ListingType::PRODUCT->value,
                'condition'       => ListingCondition::USED->value,
                'title'           => 'Офисный стол',
                'description'     => 'Стол для кабинета.',
                'price'           => 14000,
                'currency'        => 'RUB',
                'isNegotiable'    => false,
                'attributeValues' => [
                    $materialAttribute->id => 'Дерево',
                ],
            ])
            ->assertOk()
            ->assertJsonCount(1, 'data.attributeValues')
            ->assertJsonPath('data.attributeValues.0.displayValue', 'Дерево');

        $this->assertDatabaseMissing('listing_attribute_values', [
            'listing_id'              => $listing->id,
            'attribute_definition_id' => $colorAttribute->id,
        ]);
    }

    public function test_user_cannot_show_another_users_listing(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $otherUser          = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Телефоны',
            'slug'         => 'telefony',
            'catalog_type' => 1,
        ]);
        $listing            = EloquentListing::query()->create([
            'user_id'       => $otherUser->id,
            'category_id'   => $category->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::DRAFT,
            'condition'     => ListingCondition::USED,
            'title'         => 'Чужой iPhone',
            'slug'          => 'chuzhoj-iphone',
            'description'   => 'Это объявление не принадлежит текущему пользователю.',
            'price'         => 70000,
            'currency'      => 'RUB',
            'is_negotiable' => false,
        ]);

        $this
            ->actingAs($user)
            ->getJson('/api/v1/listings/' . $listing->id)
            ->assertNotFound();
    }
}
