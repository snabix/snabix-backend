<?php

declare(strict_types=1);

namespace App\Auth\Domain\Events;

use App\Auth\Domain\Entities\User;
use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class UserEmailVerified implements LoggableEvent
{
    public function __construct(
        public User $user,
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
        return 'Email пользователя успешно подтвержден.';
    }

    public function logAction(): ?string
    {
        return 'auth.verify-email';
    }

    public function logContext(): ?array
    {
        return [
            'email' => $this->user->email->value(),
        ];
    }

    public function logUserId(): ?string
    {
        return $this->user->id->value();
    }
}
