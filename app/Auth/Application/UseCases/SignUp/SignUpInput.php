<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\SignUp;

use App\Shared\Domain\DTO\Input;

class SignUpInput extends Input
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $passwordConfirmation,
    ) {}
}
