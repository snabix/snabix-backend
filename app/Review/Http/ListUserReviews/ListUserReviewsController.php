<?php

declare(strict_types=1);

namespace App\Review\Http\ListUserReviews;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Review\Application\Services\UserReviewPayloadMapper;
use App\Review\Domain\Enums\UserReviewStatus;
use App\Review\Infrastructure\Models\EloquentUserReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListUserReviewsController
{
    public function __invoke(
        string $userId,
        Request $request,
        UserReviewPayloadMapper $mapper,
    ): JsonResponse {
        abort_unless(EloquentUser::query()->whereKey($userId)->exists(), 404);

        $paginator = EloquentUserReview::query()
            ->with(['reviewer', 'listing'])
            ->where('reviewee_id', $userId)
            ->where('status', UserReviewStatus::PUBLISHED)
            ->latest()
            ->paginate(
                perPage: min(max($request->integer('perPage', 10), 1), 30),
            );

        return response()->json(['data' => [
            'items' => $paginator->getCollection()
                ->map(fn(EloquentUserReview $review): array => $mapper->map($review))
                ->values(),
            'meta'  => [
                'currentPage' => $paginator->currentPage(),
                'lastPage'    => $paginator->lastPage(),
                'perPage'     => $paginator->perPage(),
                'total'       => $paginator->total(),
            ],
        ]]);
    }
}
