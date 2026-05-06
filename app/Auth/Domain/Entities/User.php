<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entities;

use App\Auth\Domain\ValueObjects\Name;
use App\Shared\Domain\ValueObjects\Email;
use App\Shared\Domain\ValueObjects\Password;
use App\Shared\Domain\ValueObjects\UUID;
use DateTimeImmutable;

class User
{
    public function __construct(
        public UUID $id,
        public Name $name,
        public Email $email,
        public Password $password,
        public ?DateTimeImmutable $emailVerifiedAt = null,
    ) {}

    public function verifyEmail(): void
    {
        $this->emailVerifiedAt = new DateTimeImmutable();
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }
}
