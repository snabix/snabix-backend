<?php

declare(strict_types=1);

namespace App\Notification\Application\Listeners;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Domain\Events\ListingFavorited;
use App\Notification\Application\Notifications\PlatformNotification;
use App\Notification\Application\Services\PlatformNotificationDispatcher;
use App\Notification\Domain\Enums\NotificationEventType;

readonly class SendListingFavoritedNotification
{
    public function __construct(
        private PlatformNotificationDispatcher $notificationDispatcher,
    ) {}

    public function handle(ListingFavorited $event): void
    {
        $owner = EloquentUser::query()->find($event->listing->user_id);

        if (! $owner instanceof EloquentUser) {
            return;
        }

        $this->notificationDispatcher->dispatch($owner, new PlatformNotification(
            eventType: NotificationEventType::FAVORITE_LISTINGS,
            title: 'Объявление добавили в избранное',
            body: sprintf('Ваше объявление «%s» добавили в избранное.', $event->listing->title),
            actionUrl: '/account/listings/' . $event->listing->id,
            context: [
                'listingId'         => $event->listing->id,
                'listingTitle'      => $event->listing->title,
                'favoritedByUserId' => $event->favoritedByUserId,
            ],
        ));
    }
}
