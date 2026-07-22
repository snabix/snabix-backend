<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Review\Infrastructure\Models\EloquentUserReview;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTestCase;

class MarketplaceWriteInvariantTest extends FeatureTestCase
{
    private int $savepointNumber = 0;

    public function test_database_rejects_invalid_listing_values(): void
    {
        $listing = EloquentListing::factory()->create();

        $this->assertConstraintRejects(
            'listings_type_check',
            fn() => DB::table('listings')->where('id', $listing->id)->update(['type' => 99]),
        );
        $this->assertConstraintRejects(
            'listings_status_check',
            fn() => DB::table('listings')->where('id', $listing->id)->update(['status' => 99]),
        );
        $this->assertConstraintRejects(
            'listings_condition_check',
            fn() => DB::table('listings')->where('id', $listing->id)->update(['condition' => 99]),
        );
        $this->assertConstraintRejects(
            'listings_price_check',
            fn() => DB::table('listings')->where('id', $listing->id)->update(['price' => -1]),
        );
        $this->assertConstraintRejects(
            'listings_currency_check',
            fn() => DB::table('listings')->where('id', $listing->id)->update(['currency' => 'rub']),
        );
        $this->assertConstraintRejects(
            'listings_views_count_check',
            fn() => DB::table('listings')->where('id', $listing->id)->update(['views_count' => -1]),
        );
    }

    public function test_database_rejects_invalid_review_values(): void
    {
        $reviewer = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $listing  = EloquentListing::factory()->published()->create(['user_id' => $seller->id]);

        $this->assertConstraintRejects(
            'user_reviews_rating_check',
            fn() => DB::table('user_reviews')->insert([
                'id'           => (string) Str::uuid(),
                'reviewer_id'  => $reviewer->id,
                'reviewee_id'  => $seller->id,
                'listing_id'   => $listing->id,
                'rating'       => 0,
                'status'       => 'published',
                'published_at' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]),
        );

        $review   = EloquentUserReview::query()->create([
            'reviewer_id'  => $reviewer->id,
            'reviewee_id'  => $seller->id,
            'listing_id'   => $listing->id,
            'rating'       => 5,
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $this->assertConstraintRejects(
            'user_reviews_rating_check',
            fn() => DB::table('user_reviews')->where('id', $review->id)->update(['rating' => 0]),
        );
        $this->assertConstraintRejects(
            'user_reviews_status_check',
            fn() => DB::table('user_reviews')->where('id', $review->id)->update(['status' => 'unknown']),
        );
        $this->assertConstraintRejects(
            'user_reviews_distinct_participants_check',
            fn() => DB::table('user_reviews')
                ->where('id', $review->id)
                ->update(['reviewee_id' => $reviewer->id]),
        );
    }

    public function test_database_rejects_invalid_seller_rating_aggregates(): void
    {
        $seller = EloquentUser::factory()->create();

        $this->assertConstraintRejects(
            'users_seller_rating_avg_check',
            fn() => DB::table('users')
                ->where('id', $seller->id)
                ->update(['seller_reviews_count' => 1, 'seller_rating_avg' => 0]),
        );
        $this->assertConstraintRejects(
            [
                'users_seller_reviews_count_check',
                'users_seller_rating_aggregate_check',
            ],
            fn() => DB::table('users')
                ->where('id', $seller->id)
                ->update(['seller_reviews_count' => -1, 'seller_rating_avg' => 1]),
        );
        $this->assertConstraintRejects(
            'users_seller_rating_aggregate_check',
            fn() => DB::table('users')
                ->where('id', $seller->id)
                ->update(['seller_reviews_count' => 0, 'seller_rating_avg' => 4]),
        );
    }

    /**
     * @param string|list<string> $constraints
     */
    private function assertConstraintRejects(string | array $constraints, Closure $write): void
    {
        $constraintNames = is_string($constraints) ? [$constraints] : $constraints;
        $savepoint       = 'marketplace_invariant_' . ++$this->savepointNumber;

        DB::statement(sprintf('SAVEPOINT %s', $savepoint));

        try {
            $write();
            self::fail(sprintf(
                'Constraints [%s] accepted an invalid direct write.',
                implode(', ', $constraintNames),
            ));
        } catch (QueryException $exception) {
            $matched = collect($constraintNames)->contains(
                fn(string $constraint): bool => str_contains(
                    $exception->getMessage(),
                    sprintf('"%s"', $constraint),
                ),
            );

            self::assertTrue(
                $matched,
                sprintf(
                    'Expected one of constraints [%s], got: %s',
                    implode(', ', $constraintNames),
                    $exception->getMessage(),
                ),
            );
        } finally {
            DB::statement(sprintf('ROLLBACK TO SAVEPOINT %s', $savepoint));
            DB::statement(sprintf('RELEASE SAVEPOINT %s', $savepoint));
        }
    }
}
