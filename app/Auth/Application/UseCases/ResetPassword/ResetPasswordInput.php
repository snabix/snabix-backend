<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ResetPassword;

use App\Shared\Domain\DTO\Input;

class ResetPasswordInput extends Input
{
    public function __construct(
        public readonly string $email,
        public readonly string $token,
        public readonly string $password,
    ) {}
}
