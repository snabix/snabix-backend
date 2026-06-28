<?php

declare(strict_types=1);

namespace App\Bot\Application;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;

final class BotStatsService
{
    /**
     * @return array<string, int>
     */
    public function summary(): array
    {
        return [
            'usersTotal'             => EloquentUser::query()->count(),
            'listingsTotal'          => EloquentListing::query()->count(),
            'listingsPendingReview'  => EloquentListing::query()
                ->where('status', ListingStatus::PENDING_REVIEW)
                ->count(),
            'listingsPublished'      => EloquentListing::query()
                ->where('status', ListingStatus::PUBLISHED)
                ->count(),
            'listingsArchived'       => EloquentListing::query()
                ->where('status', ListingStatus::ARCHIVED)
                ->count(),
        ];
    }
}
