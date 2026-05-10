<?php

declare(strict_types=1);

namespace App\Auth\Domain\Events;

use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class UserAvatarUpdated implements LoggableEvent
{
    public function __construct(
        public string $userId,
        public int $mediaId,
    ) {}

    public function logLevel(): SystemLogLevel
    {
        return SystemLogLevel::INFO;
    }

    public function logCategory(): string
    {
        return 'auth';
    }

    public function logMessage(): string
    {
        return 'Аватар пользователя успешно обновлен.';
    }

    public function logAction(): ?string
    {
        return 'auth.profile.avatar.update';
    }

    public function logContext(): ?array
    {
        return [
            'media_id' => $this->mediaId,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->userId;
    }
}
