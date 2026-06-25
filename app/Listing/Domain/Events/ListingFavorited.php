<?php

declare(strict_types=1);

namespace App\Listing\Domain\Events;

use App\Listing\Infrastructure\Models\EloquentListing;
use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class ListingFavorited implements LoggableEvent
{
    public function __construct(
        public EloquentListing $listing,
        public string $favoritedByUserId,
    ) {}

    public function logLevel(): SystemLogLevel
    {
        return SystemLogLevel::INFO;
    }

    public function logCategory(): string
    {
        return 'listing';
    }

    public function logMessage(): string
    {
        return 'Объявление добавлено в избранное.';
    }

    public function logAction(): ?string
    {
        return 'listing.favorite';
    }

    /**
     * @return array<string, mixed>
     */
    public function logContext(): array
    {
        return [
            'listing_id'            => $this->listing->id,
            'title'                 => $this->listing->title,
            'listing_owner_user_id' => $this->listing->user_id,
            'favorited_by_user_id'  => $this->favoritedByUserId,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->favoritedByUserId;
    }
}
