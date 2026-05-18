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

class ListAndDeleteListingTest extends FeatureTestCase
{
    public function test_user_can_list_and_delete_own_listings(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $otherUser          = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Шкафы',
            'slug'         => 'shkafy',
            'catalog_type' => 1,
        ]);
        $listing            = EloquentListing::query()->create([
            'user_id'       => $user->id,
            'category_id'   => $category->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::DRAFT,
            'condition'     => ListingCondition::USED,
            'title'         => 'Шкаф купе',
            'slug'          => 'shkaf-kupe',
            'description'   => 'Большой шкаф в хорошем состоянии.',
            'price'         => 18000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
        ]);
        EloquentListing::query()->create([
            'user_id'       => $otherUser->id,
            'category_id'   => $category->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::DRAFT,
            'condition'     => ListingCondition::USED,
            'title'         => 'Чужое объявление',
            'slug'          => 'chuzhoe-obyavlenie',
            'description'   => 'Не должно попасть в список.',
            'price'         => 9900,
            'currency'      => 'RUB',
            'is_negotiable' => false,
        ]);

        $this
            ->actingAs($user)
            ->getJson('/api/v1/listings')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $listing->id)
            ->assertJsonPath('data.items.0.title', 'Шкаф купе')
            ->assertJsonPath('data.meta.currentPage', 1)
            ->assertJsonPath('data.meta.perPage', 12)
            ->assertJsonPath('data.meta.total', 1);

        $this
            ->actingAs($user)
            ->deleteJson('/api/v1/listings/' . $listing->id)
            ->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertDatabaseMissing('listings', [
            'id' => $listing->id,
        ]);
    }

    public function test_user_can_paginate_and_filter_own_listings(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Стулья',
            'slug'         => 'stulya',
            'catalog_type' => 1,
        ]);

        $draftListing       = $this->createListing(
            userId: $user->id,
            categoryId: $category->id,
            status: ListingStatus::DRAFT,
            title: 'Черновик стула',
            slug: 'chernovik-stula',
        );
        $publishedListing   = $this->createListing(
            userId: $user->id,
            categoryId: $category->id,
            status: ListingStatus::PUBLISHED,
            title: 'Опубликованный стул',
            slug: 'opublikovannyj-stul',
        );

        $this
            ->actingAs($user)
            ->getJson('/api/v1/listings?status=' . ListingStatus::DRAFT->value . '&perPage=1&page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $draftListing->id)
            ->assertJsonPath('data.meta.currentPage', 1)
            ->assertJsonPath('data.meta.perPage', 1)
            ->assertJsonPath('data.meta.total', 1);

        $secondPageResponse = $this
            ->actingAs($user)
            ->getJson('/api/v1/listings?perPage=1&page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.meta.currentPage', 2)
            ->assertJsonPath('data.meta.perPage', 1)
            ->assertJsonPath('data.meta.total', 2);

        $this->assertContains(
            $secondPageResponse->json('data.items.0.id'),
            [$draftListing->id, $publishedListing->id],
        );
    }

    private function createListing(
        string $userId,
        int $categoryId,
        ListingStatus $status,
        string $title,
        string $slug,
    ): EloquentListing {
        return EloquentListing::query()->create([
            'user_id'       => $userId,
            'category_id'   => $categoryId,
            'type'          => ListingType::PRODUCT,
            'status'        => $status,
            'condition'     => ListingCondition::USED,
            'title'         => $title,
            'slug'          => $slug,
            'description'   => 'Описание объявления.',
            'price'         => 18000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
        ]);
    }
}
