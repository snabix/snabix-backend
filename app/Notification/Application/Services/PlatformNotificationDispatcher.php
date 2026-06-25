<?php

declare(strict_types=1);

namespace App\Notification\Application\Services;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Notification\Application\Notifications\PlatformNotification;

readonly class PlatformNotificationDispatcher
{
    public function __construct(
        private NotificationPreferenceService $notificationPreferenceService,
    ) {}

    public function dispatch(EloquentUser $user, PlatformNotification $notification): void
    {
        $userId   = $user->getKey();

        if (! is_string($userId)) {
            return;
        }

        $channels = $this->notificationPreferenceService->channelsFor($userId, $notification->eventType);

        if (in_array('database', $channels, true)) {
            $user->notifyNow($notification->forChannels(['database']));
        }

        if (in_array('mail', $channels, true)) {
            $user->notify($notification->forChannels(['mail']));
        }
    }
}
