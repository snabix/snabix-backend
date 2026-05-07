<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ResetPassword;

use App\Shared\Domain\DTO\Output;

class ResetPasswordOutput extends Output
{
    public function __construct(
        public readonly bool $reset,
        public readonly string $message,
    ) {}
}
