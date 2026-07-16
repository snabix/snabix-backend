<?php

declare(strict_types=1);

namespace App\Review\Application\Services;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Review\Domain\Enums\UserReviewStatus;
use App\Review\Infrastructure\Models\EloquentUserReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class UserReviewService
{
    /**
     * @throws ValidationException
     */
    public function createPublishedReview(
        string $reviewerId,
        string $revieweeId,
        string $listingId,
        int $rating,
        ?string $comment,
    ): EloquentUserReview {
        if ($reviewerId === $revieweeId) {
            throw ValidationException::withMessages([
                'revieweeId' => 'Нельзя оставить отзыв самому себе.',
            ]);
        }

        return DB::transaction(function () use ($reviewerId, $revieweeId, $listingId, $rating, $comment): EloquentUserReview {
            $listing         = EloquentListing::query()
                ->whereKey($listingId)
                ->where('status', ListingStatus::PUBLISHED)
                ->first();

            if (! $listing instanceof EloquentListing) {
                throw ValidationException::withMessages([
                    'listingId' => 'Можно оставить отзыв только по опубликованному объявлению.',
                ]);
            }

            if ($listing->user_id !== $revieweeId) {
                throw ValidationException::withMessages([
                    'listingId' => 'Объявление не принадлежит выбранному пользователю.',
                ]);
            }

            $alreadyReviewed = EloquentUserReview::query()
                ->where('reviewer_id', $reviewerId)
                ->where('listing_id', $listingId)
                ->exists();

            if ($alreadyReviewed) {
                throw ValidationException::withMessages([
                    'listingId' => 'Вы уже оставили отзыв по этому объявлению.',
                ]);
            }

            $review          = EloquentUserReview::query()->create([
                'reviewer_id'  => $reviewerId,
                'reviewee_id'  => $revieweeId,
                'listing_id'   => $listingId,
                'rating'       => $rating,
                'comment'      => $comment,
                'status'       => UserReviewStatus::PUBLISHED,
                'published_at' => now(),
            ]);

            $this->refreshSellerRating($revieweeId);

            return $review->load(['reviewer', 'listing']);
        });
    }

    public function refreshSellerRating(string $revieweeId): void
    {
        $stats        = DB::table('user_reviews')
            ->where('reviewee_id', $revieweeId)
            ->where('status', UserReviewStatus::PUBLISHED->value)
            ->selectRaw('count(*) as reviews_count, avg(rating) as rating_avg')
            ->first();

        if ($stats === null) {
            $reviewsCount = 0;
            $ratingAvg    = null;
        } else {
            $reviewsCount = (int) $stats->reviews_count;
            $ratingAvg    = $reviewsCount > 0
                ? round((float) $stats->rating_avg, 2)
                : null;
        }

        EloquentUser::query()
            ->whereKey($revieweeId)
            ->update([
                'seller_reviews_count' => $reviewsCount,
                'seller_rating_avg'    => $ratingAvg,
            ]);
    }
}
