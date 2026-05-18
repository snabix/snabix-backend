<?php

declare(strict_types=1);

namespace App\Listing\Domain\Events;

use App\Listing\Infrastructure\Models\EloquentListing;
use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class ListingCreated implements LoggableEvent
{
    public function __construct(
        public EloquentListing $listing,
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
        return 'Объявление успешно создано.';
    }

    public function logAction(): ?string
    {
        return 'listing.create';
    }

    /**
     * @return array<string, mixed>
     */
    public function logContext(): array
    {
        return [
            'listing_id'  => $this->listing->id,
            'title'       => $this->listing->title,
            'status'      => $this->listing->status->value,
            'category_id' => $this->listing->category_id,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->listing->user_id;
    }
}
