<?php

declare(strict_types=1);

namespace App\Capability\Application;

use App\Notification\Domain\Enums\NotificationEventType;

final class PlatformCapabilityService
{
    /**
     * @return array{
     *     account: array{deactivation: bool, deletion: bool},
     *     notifications: array{eventKeys: list<string>},
     *     sellerProfiles: array{enabled: bool}
     * }
     */
    public function contract(): array
    {
        return [
            'account'        => [
                'deactivation' => false,
                'deletion'     => false,
            ],
            'notifications'  => [
                'eventKeys' => array_map(
                    static fn(NotificationEventType $type): string => $type->value,
                    NotificationEventType::availableCases(),
                ),
            ],
            'sellerProfiles' => [
                'enabled' => false,
            ],
        ];
    }
}
