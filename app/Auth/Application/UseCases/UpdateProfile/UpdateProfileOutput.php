<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\UpdateProfile;

use App\Shared\Domain\DTO\Output;

class UpdateProfileOutput extends Output
{
    /**
     * @param array<string, mixed>|null  $avatar
     * @param list<array<string, mixed>> $addresses
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
        public readonly array $addresses,
    ) {}
}
