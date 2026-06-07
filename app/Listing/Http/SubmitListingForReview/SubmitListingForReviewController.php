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
        $result = $handler->execute(SubmitListingForReviewInput::from($request->inputData()));

        return SubmitListingForReviewResponse::make($result);
    }
}
