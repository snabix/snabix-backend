<?php

declare(strict_types=1);

namespace App\Listing\Http\SubmitListingForReview;

use App\Listing\Application\UseCases\SubmitListingForReview\SubmitListingForReviewOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin SubmitListingForReviewOutput
 */
class SubmitListingForReviewResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
