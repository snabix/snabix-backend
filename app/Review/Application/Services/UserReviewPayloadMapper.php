<?php

declare(strict_types=1);

namespace App\Review\Application\Services;

use App\Review\Infrastructure\Models\EloquentUserReview;

final readonly class UserReviewPayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(EloquentUserReview $review): array
    {
        return [
            'id'                => $review->id,
            'reviewer'          => [
                'id'        => $review->reviewer->id,
                'firstName' => $review->reviewer->first_name,
                'lastName'  => $review->reviewer->last_name,
            ],
            'revieweeId'        => $review->reviewee_id,
            'listing'           => [
                'id'    => $review->listing->id,
                'title' => $review->listing->title,
            ],
            'rating'            => $review->rating,
            'comment'           => $review->comment,
            'reviewStatus'      => $review->status->value,
            'reviewStatusLabel' => $review->status->label(),
            // Deprecated compatibility aliases. Remove after 2026-10-31.
            'status'            => $review->status->value,
            'statusLabel'       => $review->status->label(),
            'createdAt'         => $review->created_at?->toIso8601String(),
        ];
    }
}
