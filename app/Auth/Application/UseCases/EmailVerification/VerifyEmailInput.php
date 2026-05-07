<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\EmailVerification;

use App\Shared\Domain\DTO\Input;

class VerifyEmailInput extends Input
{
    public function __construct(
        public readonly string $userId,
    ) {}
}
