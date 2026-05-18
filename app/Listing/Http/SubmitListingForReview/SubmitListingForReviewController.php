<?php

declare(strict_types=1);

namespace App\Listing\Http\SubmitListingForReview;

use App\Listing\Application\UseCases\SubmitListingForReview\SubmitListingForReviewHandler;
use App\Listing\Application\UseCases\SubmitListingForReview\SubmitListingForReviewInput;

class SubmitListingForReviewController
{
    public function __invoke(
        SubmitListingForReviewRequest $request,
        SubmitListingForReviewHandler $handler,
    ): SubmitListingForReviewResponse {
        $request->validated();

        $result = $handler->execute(
            SubmitListingForReviewInput::from([
                'userId'    => $request->userId(),
                'listingId' => $request->listingId(),
            ]),
        );

        return SubmitListingForReviewResponse::make($result);
    }
}
