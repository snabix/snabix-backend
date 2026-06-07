<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ForgotPassword;

use App\Shared\Domain\DTO\Input;

class ForgotPasswordInput extends Input
{
    public function __construct(
        public readonly string $email,
    ) {}
}
