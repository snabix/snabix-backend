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

class ListingFavoriteTest extends FeatureTestCase
{
    public function test_user_can_add_list_and_remove_favorite_listing(): void
    {
        $user     = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $category = app(CategoryRepositoryInterface::class)->save([
            'name'         => 'Ноутбуки',
            'slug'         => 'noutbuki',
            'catalog_type' => 1,
        ]);
        $listing  = $this->createPublishedListing($seller->id, $category->id);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/listings/' . $listing->id . '/favorite')
            ->assertOk()
            ->assertJsonPath('data.id', $listing->id)
            ->assertJsonPath('data.isFavorite', true);

        $this->assertDatabaseHas('listing_favorites', [
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
        ]);

        $this
            ->actingAs($user)
            ->getJson('/api/v1/listings/favorites')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $listing->id)
            ->assertJsonPath('data.items.0.isFavorite', true);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->delete('/api/v1/listings/' . $listing->id . '/favorite')
            ->assertOk()
            ->assertJsonPath('data.isFavorite', false);

        $this->assertDatabaseMissing('listing_favorites', [
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
        ]);
    }

    public function test_draft_listing_cannot_be_added_to_favorites(): void
    {
        $user     = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $category = app(CategoryRepositoryInterface::class)->save([
            'name'         => 'Черновики',
            'slug'         => 'chernoviki',
            'catalog_type' => 1,
        ]);
        $listing  = $this->createPublishedListing($seller->id, $category->id, [
            'status' => ListingStatus::DRAFT,
        ]);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/listings/' . $listing->id . '/favorite')
            ->assertNotFound();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createPublishedListing(string $userId, string $categoryId, array $overrides = []): EloquentListing
    {
        return EloquentListing::query()->create([
            'user_id'       => $userId,
            'category_id'   => $categoryId,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::PUBLISHED,
            'condition'     => ListingCondition::USED,
            'title'         => 'Игровой ноутбук',
            'slug'          => 'igrovoj-noutbuk',
            'description'   => 'Ноутбук в хорошем состоянии.',
            'price'         => 85000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
            'published_at'  => now(),
            ...$overrides,
        ]);
    }
}
