<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ArchiveListing;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Events\ListingUpdated;
use App\Listing\Domain\Exceptions\InvalidListingStatusTransitionException;
use App\Listing\Domain\Services\ListingStatusTransitionPolicy;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

readonly class ArchiveListingHandler
{
    public function __construct(
        private ListingRepositoryInterface $listingRepository,
        private ListingPayloadMapper $listingPayloadMapper,
        private ListingStatusTransitionPolicy $listingStatusTransitionPolicy,
    ) {}

    public function execute(ArchiveListingInput $input): ArchiveListingOutput
    {
        $listing        = $this->listingRepository->findById($input->listingId);

        if ($listing === null) {
            throw (new ModelNotFoundException())->setModel(EloquentListing::class, [$input->listingId]);
        }

        Gate::authorize('update', $listing);

        try {
            $this->listingStatusTransitionPolicy->assertCanTransition(
                from: $listing->status,
                to: ListingStatus::ARCHIVED,
            );
        } catch (InvalidListingStatusTransitionException) {
            throw ValidationException::withMessages([
                'status' => ['Объявление нельзя архивировать из текущего статуса.'],
            ]);
        }

        $previousStatus = $listing->status;
        $listing        = $this->listingRepository->transitionStatus($listing, ListingStatus::ARCHIVED);

        event(new ListingUpdated($listing, [
            'status' => [
                'from' => $previousStatus->value,
                'to'   => ListingStatus::ARCHIVED->value,
            ],
        ]));

        return ArchiveListingOutput::from([
            'item' => $this->listingPayloadMapper->map($listing),
        ]);
    }
}
