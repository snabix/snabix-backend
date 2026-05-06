<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\SignIn;

use App\Shared\Domain\DTO\Input;

class SignInInput extends Input
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
