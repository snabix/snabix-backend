<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ChangePassword;

use App\Shared\Domain\DTO\Input;

class ChangePasswordInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly string $currentPassword,
        public readonly string $password,
    ) {}
}
