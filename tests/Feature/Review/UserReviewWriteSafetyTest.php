<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTestCase;

class UserReviewWriteSafetyTest extends FeatureTestCase
{
    use CreatesPublishedListingForReview;

    public function test_create_review_replays_same_result_for_same_idempotency_key(): void
    {
        $reviewer = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $listing  = $this->createPublishedListing($seller->id);
        $payload  = [
            'listingId' => $listing->id,
            'rating'    => 5,
            'comment'   => 'Надежный продавец.',
        ];
        $key      = 'review-019f4f54-19c2-7f39-a778-e328b85cd690';

        $first    = $this
            ->actingAs($reviewer)
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', $payload)
            ->assertCreated();

        $second   = $this
            ->actingAs($reviewer)
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', $payload)
            ->assertCreated();

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('user_reviews', 1);

        $seller->refresh();

        $this->assertSame(5.0, $seller->seller_rating_avg);
        $this->assertSame(1, $seller->seller_reviews_count);
    }

    public function test_create_review_rejects_reused_idempotency_key_for_changed_payload(): void
    {
        $reviewer = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $listing  = $this->createPublishedListing($seller->id);
        $key      = 'review-019f4f54-19c2-7f39-a778-e328b85cd691';

        $this
            ->actingAs($reviewer)
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $listing->id,
                'rating'    => 5,
            ])
            ->assertCreated();

        $this
            ->actingAs($reviewer)
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $listing->id,
                'rating'    => 3,
            ])
            ->assertConflict()
            ->assertJsonPath('code', 'request.idempotency-conflict');

        $this->assertDatabaseCount('user_reviews', 1);
    }

    public function test_concurrent_duplicate_review_unique_violation_returns_validation_error(): void
    {
        $reviewer = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $listing  = $this->createPublishedListing($seller->id);
        $injected = false;

        DB::listen(function (QueryExecuted $query) use (
            $reviewer,
            $seller,
            $listing,
            &$injected,
        ): void {
            if (
                $injected
                || ! str_contains($query->sql, 'select exists')
                || ! str_contains($query->sql, '"user_reviews"')
                || ! in_array($reviewer->id, $query->bindings, true)
                || ! in_array($listing->id, $query->bindings, true)
            ) {
                return;
            }

            $injected = true;

            DB::table('user_reviews')->insert([
                'id'           => (string) Str::uuid(),
                'reviewer_id'  => $reviewer->id,
                'reviewee_id'  => $seller->id,
                'listing_id'   => $listing->id,
                'rating'       => 4,
                'status'       => 'published',
                'published_at' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        });

        $this
            ->actingAs($reviewer)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $listing->id,
                'rating'    => 5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['listingId']);

        $this->assertTrue($injected);
        $this->assertDatabaseCount('user_reviews', 0);
    }

    public function test_seller_rating_aggregate_includes_multiple_serialized_reviews(): void
    {
        $firstReviewer  = EloquentUser::factory()->create();
        $secondReviewer = EloquentUser::factory()->create();
        $seller         = EloquentUser::factory()->create();
        $firstListing   = $this->createPublishedListing($seller->id);
        $secondListing  = $this->createPublishedListing($seller->id);

        $this
            ->actingAs($firstReviewer)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $firstListing->id,
                'rating'    => 5,
            ])
            ->assertCreated();

        $this
            ->actingAs($secondReviewer)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [
                'listingId' => $secondListing->id,
                'rating'    => 3,
            ])
            ->assertCreated();

        $seller->refresh();

        $this->assertSame(4.0, $seller->seller_rating_avg);
        $this->assertSame(2, $seller->seller_reviews_count);
    }
}
