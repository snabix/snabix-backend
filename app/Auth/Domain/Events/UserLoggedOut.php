<?php

declare(strict_types=1);

namespace App\Auth\Domain\Events;

use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class UserLoggedOut implements LoggableEvent
{
    public function __construct(
        public string $userId,
        public ?string $tokenId,
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
        return 'Пользователь вышел из системы.';
    }

    public function logAction(): ?string
    {
        return 'auth.logout';
    }

    public function logContext(): ?array
    {
        return [
            'token_id' => $this->tokenId,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->userId;
    }
}
