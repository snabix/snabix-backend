<?php

declare(strict_types=1);

namespace App\Listing\Domain\Events;

use App\Listing\Domain\Enums\ListingStatus;
use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class ListingDeleted implements LoggableEvent
{
    public function __construct(
        public string $listingId,
        public string $userId,
        public string $title,
        public ListingStatus $status,
        public int $categoryId,
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
        return 'Объявление успешно удалено.';
    }

    public function logAction(): ?string
    {
        return 'listing.delete';
    }

    /**
     * @return array<string, mixed>
     */
    public function logContext(): array
    {
        return [
            'listing_id'  => $this->listingId,
            'title'       => $this->title,
            'status'      => $this->status->value,
            'category_id' => $this->categoryId,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->userId;
    }
}
