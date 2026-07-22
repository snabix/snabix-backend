<?php

declare(strict_types=1);

namespace App\Review\Http\CreateUserReview;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Review\Application\Services\UserReviewPayloadMapper;
use App\Review\Application\Services\UserReviewService;
use Dedoc\Scramble\Attributes\HeaderParameter;
use Illuminate\Http\JsonResponse;

#[HeaderParameter(
    'Idempotency-Key',
    description: 'Необязательный уникальный ключ повтора create-запроса, 8-128 символов.',
    required: false,
    type: 'string',
    example: 'review-019f4f54-19c2-7f39-a778-e328b85cd690',
)]
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
            idempotencyKey: $request->idempotencyKey(),
        );

        return response()->json(['data' => $mapper->map($review)], 201);
    }
}
