<?php

declare(strict_types=1);

namespace App\Notification\Application\Listeners;

use App\Auth\Application\Services\SessionClientInfo;
use App\Auth\Domain\Events\UserSignedIn;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Notification\Application\Notifications\PlatformNotification;
use App\Notification\Application\Services\PlatformNotificationDispatcher;
use App\Notification\Domain\Enums\NotificationEventType;
use Carbon\CarbonImmutable;

readonly class SendSecurityLoginNotification
{
    public function __construct(
        private PlatformNotificationDispatcher $notificationDispatcher,
        private SessionClientInfo $clientInfo,
    ) {}

    public function handle(UserSignedIn $event): void
    {
        $user         = EloquentUser::query()->find($event->user->id->value());

        if (! $user instanceof EloquentUser) {
            return;
        }

        $signedInAt   = $event->signedInAt ?? CarbonImmutable::now();
        $timezone     = config('app.timezone', 'UTC');
        $timezone     = is_string($timezone) && $timezone !== '' ? $timezone : 'UTC';
        $loginDetails = [
            'location'   => $this->clientInfo->locationLabel($event->ipAddress),
            'device'     => $this->clientInfo->deviceName($event->userAgent),
            'browser'    => $this->clientInfo->browser($event->userAgent),
            'ipAddress'  => $event->ipAddress ?? 'неизвестно',
            'signedInAt' => $signedInAt->setTimezone($timezone)->format('d.m.Y H:i:s T'),
        ];

        $this->notificationDispatcher->dispatch($user, new PlatformNotification(
            eventType: NotificationEventType::SECURITY_LOGIN,
            title: 'Выполнен вход в аккаунт',
            body: 'Мы зафиксировали успешный вход в ваш аккаунт SNABIX. Если это были вы, дополнительных действий не требуется.',
            actionUrl: '/account/settings/sessions',
            context: [
                'loginDetails' => $loginDetails,
            ],
        ));
    }
}
