<?php

declare(strict_types=1);

namespace App\Auth\Domain\Events;

use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class AuthenticationFailed implements LoggableEvent
{
    public function __construct(
        public string $email,
        public string $reason,
        public ?string $userId = null,
    ) {}

    public function logLevel(): SystemLogLevel
    {
        return SystemLogLevel::WARNING;
    }

    public function logCategory(): string
    {
        return 'auth';
    }

    public function logMessage(): string
    {
        return 'Неуспешная попытка входа в систему.';
    }

    public function logAction(): ?string
    {
        return 'auth.sign-in.failed';
    }

    public function logContext(): ?array
    {
        return [
            'email' => $this->email,
            'reason' => $this->reason,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->userId;
    }
}
