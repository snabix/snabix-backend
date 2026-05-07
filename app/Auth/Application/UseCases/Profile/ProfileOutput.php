<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\Profile;

use App\Shared\Domain\DTO\Output;

class ProfileOutput extends Output
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $emailVerifiedAt,
    ) {}
}
