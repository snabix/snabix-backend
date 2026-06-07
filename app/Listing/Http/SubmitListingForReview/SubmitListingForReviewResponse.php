<?php

declare(strict_types=1);

namespace App\Listing\Http\SubmitListingForReview;

use App\Listing\Application\UseCases\SubmitListingForReview\SubmitListingForReviewOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SubmitListingForReviewOutput
 */
class SubmitListingForReviewResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
