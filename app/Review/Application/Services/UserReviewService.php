<?php

declare(strict_types=1);

namespace App\Review\Application\Services;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Review\Domain\Enums\UserReviewStatus;
use App\Review\Infrastructure\Models\EloquentUserReview;
use App\Shared\Application\DTO\IdempotencyResult;
use App\Shared\Domain\Exceptions\IdempotencyConflictException;
use App\Shared\Infrastructure\Database\UniqueConstraintViolationDetector;
use App\Shared\Infrastructure\Services\IdempotencyService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class UserReviewService
{
    public function __construct(
        private IdempotencyService $idempotencyService,
        private UniqueConstraintViolationDetector $uniqueConstraintViolationDetector,
    ) {}

    /**
     * @throws ValidationException
     */
    public function createPublishedReview(
        string $reviewerId,
        string $revieweeId,
        string $listingId,
        int $rating,
        ?string $comment,
        ?string $idempotencyKey = null,
    ): EloquentUserReview {
        if ($reviewerId === $revieweeId) {
            throw ValidationException::withMessages([
                'revieweeId' => 'Нельзя оставить отзыв самому себе.',
            ]);
        }

        try {
            $review = $this->idempotencyService->execute(
                idempotencyKey: $idempotencyKey,
                scope: 'review.create',
                actorKey: $reviewerId,
                payload: [
                    'revieweeId' => $revieweeId,
                    'listingId'  => $listingId,
                    'rating'     => $rating,
                    'comment'    => $comment,
                ],
                operation: function () use (
                    $reviewerId,
                    $revieweeId,
                    $listingId,
                    $rating,
                    $comment,
                ): IdempotencyResult {
                    $created = $this->createReview(
                        $reviewerId,
                        $revieweeId,
                        $listingId,
                        $rating,
                        $comment,
                    );

                    return new IdempotencyResult(
                        resourceId: $created->id,
                        value: $created,
                    );
                },
                replay: fn(string $reviewId): EloquentUserReview => $this->replayReview(
                    $reviewId,
                    $reviewerId,
                    $revieweeId,
                ),
            );
        } catch (UniqueConstraintViolationException $exception) {
            if (! $this->uniqueConstraintViolationDetector->matches(
                $exception,
                'user_reviews_reviewer_id_listing_id_unique',
            )) {
                throw $exception;
            }

            $this->throwAlreadyReviewed();
        }

        return $review;
    }

    private function createReview(
        string $reviewerId,
        string $revieweeId,
        string $listingId,
        int $rating,
        ?string $comment,
    ): EloquentUserReview {
        $seller          = EloquentUser::query()
            ->whereKey($revieweeId)
            ->lockForUpdate()
            ->first();

        if (! $seller instanceof EloquentUser) {
            throw ValidationException::withMessages([
                'revieweeId' => 'Пользователь для отзыва не найден.',
            ]);
        }

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
            $this->throwAlreadyReviewed();
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
    }

    private function replayReview(
        string $reviewId,
        string $reviewerId,
        string $revieweeId,
    ): EloquentUserReview {
        $review = EloquentUserReview::query()
            ->whereKey($reviewId)
            ->where('reviewer_id', $reviewerId)
            ->where('reviewee_id', $revieweeId)
            ->first();

        if (! $review instanceof EloquentUserReview) {
            throw new IdempotencyConflictException();
        }

        return $review->load(['reviewer', 'listing']);
    }

    private function throwAlreadyReviewed(): never
    {
        throw ValidationException::withMessages([
            'listingId' => 'Вы уже оставили отзыв по этому объявлению.',
        ]);
    }

    private function refreshSellerRating(string $revieweeId): void
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
