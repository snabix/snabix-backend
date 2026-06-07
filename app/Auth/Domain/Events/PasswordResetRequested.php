<?php

declare(strict_types=1);

namespace App\Auth\Domain\Events;

use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class PasswordResetRequested implements LoggableEvent
{
    public function __construct(
        public string $userId,
        public string $email,
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
        return 'Пользователь запросил восстановление пароля.';
    }

    public function logAction(): ?string
    {
        return 'auth.forgot-password';
    }

    public function logContext(): ?array
    {
        return [
            'email' => $this->email,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->userId;
    }
}
