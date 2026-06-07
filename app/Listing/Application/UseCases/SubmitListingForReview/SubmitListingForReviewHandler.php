<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\SubmitListingForReview;

use App\Listing\Application\Services\ListingRequiredAttributeValidator;
use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Events\ListingSubmittedForReview;
use App\Listing\Domain\Exceptions\InvalidListingStatusTransitionException;
use App\Listing\Domain\Services\ListingStatusTransitionPolicy;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

readonly class SubmitListingForReviewHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private ListingPayloadMapper $listingPayloadMapper,
        private ListingRequiredAttributeValidator $listingRequiredAttributeValidator,
        private ListingStatusTransitionPolicy $listingStatusTransitionPolicy,
    ) {}

    public function execute(SubmitListingForReviewInput $input): SubmitListingForReviewOutput
    {
        $listing = $this->listingRepository->findById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        Gate::authorize('submitForReview', $listing);

        try {
            $this->listingStatusTransitionPolicy->assertCanTransition(
                from: $listing->status,
                to: ListingStatus::PENDING_REVIEW,
            );
        } catch (InvalidListingStatusTransitionException) {
            throw ValidationException::withMessages([
                'status' => ['Объявление нельзя отправить на проверку из текущего статуса.'],
            ]);
        }

        $this->listingRequiredAttributeValidator->validateStoredValues(
            listing: $listing,
            categoryId: $listing->category_id,
        );

        $listing = $this->listingRepository->transitionStatus($listing, ListingStatus::PENDING_REVIEW);

        event(new ListingSubmittedForReview($listing));

        return SubmitListingForReviewOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
