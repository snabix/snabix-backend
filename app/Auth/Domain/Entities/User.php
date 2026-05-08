<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entities;

use App\Auth\Domain\ValueObjects\FirstName;
use App\Auth\Domain\ValueObjects\LastName;
use App\Auth\Domain\ValueObjects\PhoneNumber;
use App\Shared\Domain\ValueObjects\Email;
use App\Shared\Domain\ValueObjects\Password;
use App\Shared\Domain\ValueObjects\UUID;
use DateTimeImmutable;

class User
{
    public function __construct(
        public UUID $id,
        public FirstName $firstName,
        public LastName $lastName,
        public Email $email,
        public Password $password,
        public bool $isActive = true,
        public ?PhoneNumber $phoneNumber = null,
        public ?DateTimeImmutable $emailVerifiedAt = null,
    ) {}

    public function verifyEmail(): void
    {
        $this->emailVerifiedAt = new DateTimeImmutable;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function fullName(): string
    {
        return trim($this->firstName->value().' '.$this->lastName->value());
    }

    public function updateProfile(
        FirstName $firstName,
        LastName $lastName,
        Email $email,
        ?PhoneNumber $phoneNumber = null,
    ): bool {
        $emailChanged = $this->email->value() !== $email->value();

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;

        if ($emailChanged) {
            $this->emailVerifiedAt = null;
        }

        return $emailChanged;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function changePassword(Password $password): void
    {
        $this->password = $password;
    }
}
