<?php

declare(strict_types=1);

namespace App\Auth\Domain\Events;

use App\Auth\Domain\Entities\User;
use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Domain\Enums\SystemLogLevel;

readonly class UserProfileUpdated implements LoggableEvent
{
    public function __construct(
        public User $user,
        public bool $emailChanged,
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
        return 'Профиль пользователя успешно обновлен.';
    }

    public function logAction(): ?string
    {
        return 'auth.profile.update';
    }

    public function logContext(): ?array
    {
        return [
            'email'         => $this->user->email->value(),
            'email_changed' => $this->emailChanged,
        ];
    }

    public function logUserId(): ?string
    {
        return $this->user->id->value();
    }
}
