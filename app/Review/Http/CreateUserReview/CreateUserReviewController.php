<?php

declare(strict_types=1);

namespace App\Review\Http\CreateUserReview;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Review\Application\Services\UserReviewPayloadMapper;
use App\Review\Application\Services\UserReviewService;
use Illuminate\Http\JsonResponse;

class CreateUserReviewController
{
    public function __invoke(
        string $userId,
        CreateUserReviewRequest $request,
        UserReviewService $service,
        UserReviewPayloadMapper $mapper,
    ): JsonResponse {
        abort_unless(EloquentUser::query()->whereKey($userId)->exists(), 404);

        $input  = $request->inputData();
        $review = $service->createPublishedReview(
            reviewerId: $request->userId(),
            revieweeId: $userId,
            listingId: $input['listingId'],
            rating: $input['rating'],
            comment: $input['comment'],
        );

        return response()->json(['data' => $mapper->map($review)], 201);
    }
}
