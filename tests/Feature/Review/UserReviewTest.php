<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Auth\Infrastructure\Models\EloquentUser;
use Tests\Feature\FeatureTestCase;

class UserReviewTest extends FeatureTestCase
{
    use CreatesPublishedListingForReview;

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

    public function test_public_review_keeps_missing_reviewer_name_nullable(): void
    {
        $reviewer = EloquentUser::factory()->withoutName()->create();
        $seller   = EloquentUser::factory()->create();
        $listing  = $this->createPublishedListing($seller->id);

        $this
            ->actingAs($reviewer)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $listing->id,
                'rating'    => 5,
            ])
            ->assertCreated()
            ->assertJsonPath('data.reviewer.firstName', null)
            ->assertJsonPath('data.reviewer.lastName', null);
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
}
