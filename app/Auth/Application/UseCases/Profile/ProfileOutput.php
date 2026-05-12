<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\Profile;

use App\Shared\Domain\DTO\Output;

class ProfileOutput extends Output
{
    /**
     * @param array<string, mixed>|null $avatar
     */
    public function __construct(
        public readonly string $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $phoneNumber,
        public readonly bool $isActive,
        public readonly ?string $emailVerifiedAt,
        public readonly ?array $avatar,
    ) {}
}
