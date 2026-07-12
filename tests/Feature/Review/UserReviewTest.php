<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use Tests\Feature\FeatureTestCase;

class UserReviewTest extends FeatureTestCase
{
    public function test_user_can_review_seller_by_published_listing(): void
    {
        $reviewer = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $listing  = $this->createPublishedListing($seller->id);

        $response = $this
            ->actingAs($reviewer)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $listing->id,
                'rating'    => 5,
                'comment'   => 'Быстро договорились и аккуратно подготовили товар.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.comment', 'Быстро договорились и аккуратно подготовили товар.')
            ->assertJsonPath('data.revieweeId', $seller->id)
            ->assertJsonPath('data.listing.id', $listing->id);

        $this->assertDatabaseHas('user_reviews', [
            'id'          => $response->json('data.id'),
            'reviewer_id' => $reviewer->id,
            'reviewee_id' => $seller->id,
            'listing_id'  => $listing->id,
            'rating'      => 5,
            'status'      => 'published',
        ]);

        $seller->refresh();

        $this->assertSame(5.0, $seller->seller_rating_avg);
        $this->assertSame(1, $seller->seller_reviews_count);
    }

    public function test_user_cannot_review_self(): void
    {
        $seller  = EloquentUser::factory()->create();
        $listing = $this->createPublishedListing($seller->id);

        $this
            ->actingAs($seller)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $listing->id,
                'rating'    => 5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['revieweeId']);
    }

    public function test_user_cannot_review_same_listing_twice(): void
    {
        $reviewer = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $listing  = $this->createPublishedListing($seller->id);

        $payload  = [
            'listingId' => $listing->id,
            'rating'    => 4,
        ];

        $this
            ->actingAs($reviewer)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', $payload)
            ->assertCreated();

        $this
            ->actingAs($reviewer)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['listingId']);
    }

    public function test_user_can_list_public_reviews_for_seller(): void
    {
        $reviewer = EloquentUser::factory()->create([
            'first_name' => 'Иван',
            'last_name'  => 'Петров',
        ]);
        $seller   = EloquentUser::factory()->create();
        $listing  = $this->createPublishedListing($seller->id);

        $this
            ->actingAs($reviewer)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $listing->id,
                'rating'    => 4,
                'comment'   => 'Все прошло спокойно.',
            ])
            ->assertCreated();

        $this
            ->getJson('/api/v1/users/' . $seller->id . '/reviews')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.rating', 4)
            ->assertJsonPath('data.items.0.reviewer.firstName', 'Иван')
            ->assertJsonPath('data.items.0.listing.title', $listing->title);
    }

    public function test_public_listing_payload_includes_seller_rating_aggregate(): void
    {
        $reviewer = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $listing  = $this->createPublishedListing($seller->id);

        $this
            ->actingAs($reviewer)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $listing->id,
                'rating'    => 4,
            ])
            ->assertCreated();

        $this
            ->getJson('/api/v1/public/listings')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $listing->id)
            ->assertJsonPath('data.items.0.sellerRating', 4)
            ->assertJsonPath('data.items.0.sellerReviewCount', 1);
    }

    private function createPublishedListing(string $sellerId): EloquentListing
    {
        $category = app(CategoryRepositoryInterface::class)->save([
            'name'         => 'Отзывы',
            'slug'         => 'otzyvy-' . substr($sellerId, 0, 8),
            'catalog_type' => 1,
        ]);

        return EloquentListing::query()->create([
            'user_id'       => $sellerId,
            'category_id'   => $category->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::PUBLISHED,
            'condition'     => ListingCondition::USED,
            'title'         => 'Тестовое объявление',
            'slug'          => 'testovoe-obyavlenie-' . substr($sellerId, 0, 8),
            'description'   => 'Описание объявления для отзыва.',
            'price'         => 10000,
            'currency'      => 'RUB',
            'is_negotiable' => false,
            'published_at'  => now(),
        ]);
    }
}
