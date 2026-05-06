<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\SignIn;

use App\Shared\Domain\DTO\Output;

class SignInOutput extends Output
{
    public function __construct(
        public readonly string $token,
        public readonly string $userId,
    ) {}
}
