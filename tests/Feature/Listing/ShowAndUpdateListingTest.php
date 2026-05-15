<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
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
                'status'          => ListingStatus::DRAFT->value,
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
