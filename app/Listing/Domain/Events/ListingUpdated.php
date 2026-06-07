<?php

declare(strict_types=1);

namespace App\Listing\Domain\Events;

use App\Listing\Infrastructure\Models\EloquentListing;
use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class ListingUpdated implements LoggableEvent
{
    /**
     * @param array<string, array{from: mixed, to: mixed}> $changes
     */
    public function __construct(
        public EloquentListing $listing,
        public array $changes,
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
        return 'Объявление обновлено.';
    }

    public function logAction(): ?string
    {
        return 'listing.update';
    }

    /**
     * @return array<string, mixed>
     */
    public function logContext(): array
    {
        return [
            'listing_id'     => $this->listing->id,
            'title'          => $this->listing->title,
            'status'         => $this->listing->status->value,
            'category_id'    => $this->listing->category_id,
            'changed_fields' => array_keys($this->changes),
            'changes'        => $this->changes,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->listing->user_id;
    }
}
