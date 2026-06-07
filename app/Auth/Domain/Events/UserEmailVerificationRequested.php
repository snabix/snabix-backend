<?php

declare(strict_types=1);

namespace App\Auth\Domain\Events;

use App\Auth\Domain\Entities\User;
use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class UserEmailVerificationRequested implements LoggableEvent
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
        return 'Пользователь запросил повторную отправку кода подтверждения email.';
    }

    public function logAction(): ?string
    {
        return 'auth.email-verification.requested';
    }

    /**
     * @return array<string, mixed>
     */
    public function logContext(): array
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
