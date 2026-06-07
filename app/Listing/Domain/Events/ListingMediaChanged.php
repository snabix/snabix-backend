<?php

declare(strict_types=1);

namespace App\Listing\Domain\Events;

use App\Listing\Infrastructure\Models\EloquentListing;
use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class ListingMediaChanged implements LoggableEvent
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        public EloquentListing $listing,
        public string $action,
        public array $details = [],
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
        return match ($this->action) {
            'upload'   => 'Изображения объявления загружены.',
            'delete'   => 'Изображение объявления удалено.',
            'reorder'  => 'Порядок изображений объявления изменен.',
            'set-main' => 'Главное изображение объявления изменено.',
            default    => 'Медиа объявления изменены.',
        };
    }

    public function logAction(): ?string
    {
        return 'listing.media.' . $this->action;
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
            ...$this->details,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->listing->user_id;
    }
}
