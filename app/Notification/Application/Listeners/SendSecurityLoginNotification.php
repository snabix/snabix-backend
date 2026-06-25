<?php

declare(strict_types=1);

namespace App\Notification\Application\Listeners;

use App\Auth\Domain\Events\UserSignedIn;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Notification\Application\Notifications\PlatformNotification;
use App\Notification\Application\Services\PlatformNotificationDispatcher;
use App\Notification\Domain\Enums\NotificationEventType;

readonly class SendSecurityLoginNotification
{
    public function __construct(
        private PlatformNotificationDispatcher $notificationDispatcher,
    ) {}

    public function handle(UserSignedIn $event): void
    {
        $user = EloquentUser::query()->find($event->user->id->value());

        if (! $user instanceof EloquentUser) {
            return;
        }

        $this->notificationDispatcher->dispatch($user, new PlatformNotification(
            eventType: NotificationEventType::SECURITY_LOGIN,
            title: 'Выполнен вход в аккаунт',
            body: 'Мы зафиксировали успешный вход в ваш аккаунт SNABIX.',
            actionUrl: '/account/settings/sessions',
        ));
    }
}
