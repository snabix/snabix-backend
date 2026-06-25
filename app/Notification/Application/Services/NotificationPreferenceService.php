<?php

declare(strict_types=1);

namespace App\Notification\Application\Services;

use App\Notification\Domain\Enums\NotificationEventType;
use App\Notification\Infrastructure\Models\EloquentNotificationPreference;

final class NotificationPreferenceService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForUser(string $userId): array
    {
        $stored = EloquentNotificationPreference::query()
            ->where('user_id', $userId)
            ->get()
            ->keyBy('event_key');

        return array_map(function (NotificationEventType $type) use ($stored): array {
            $preference   = $stored->get($type->value);
            $siteEnabled  = $preference instanceof EloquentNotificationPreference
                ? $preference->site_enabled
                : $type->defaultSiteEnabled();
            $emailEnabled = $preference instanceof EloquentNotificationPreference
                ? $preference->email_enabled
                : $type->defaultEmailEnabled();

            return [
                'key'          => $type->value,
                'category'     => $type->category(),
                'title'        => $type->title(),
                'description'  => $type->description(),
                'siteEnabled'  => $type->isRequiredSite() || $siteEnabled,
                'emailEnabled' => $emailEnabled,
                'isRequired'   => $type->isRequiredSite(),
            ];
        }, NotificationEventType::cases());
    }

    /**
     * @param list<array{key: string, siteEnabled: bool, emailEnabled: bool}> $items
     *
     * @return list<array<string, mixed>>
     */
    public function replaceForUser(string $userId, array $items): array
    {
        foreach ($items as $item) {
            $type = NotificationEventType::from($item['key']);

            EloquentNotificationPreference::query()->updateOrCreate(
                ['user_id' => $userId, 'event_key' => $type->value],
                [
                    'site_enabled'  => $type->isRequiredSite() || $item['siteEnabled'],
                    'email_enabled' => $item['emailEnabled'],
                ],
            );
        }

        return $this->listForUser($userId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function resetForUser(string $userId): array
    {
        EloquentNotificationPreference::query()
            ->where('user_id', $userId)
            ->delete();

        return $this->listForUser($userId);
    }

    /**
     * @return list<string>
     */
    public function channelsFor(string $userId, NotificationEventType $type): array
    {
        $preference   = EloquentNotificationPreference::query()
            ->where('user_id', $userId)
            ->where('event_key', $type->value)
            ->first();
        $siteEnabled  = $preference instanceof EloquentNotificationPreference
            ? $preference->site_enabled
            : $type->defaultSiteEnabled();
        $emailEnabled = $preference instanceof EloquentNotificationPreference
            ? $preference->email_enabled
            : $type->defaultEmailEnabled();
        $channels     = [];

        if ($type->isRequiredSite() || $siteEnabled) {
            $channels[] = 'database';
        }

        if ($emailEnabled) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
