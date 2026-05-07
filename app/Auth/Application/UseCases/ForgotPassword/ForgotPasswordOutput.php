<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ForgotPassword;

use App\Shared\Domain\DTO\Output;

class ForgotPasswordOutput extends Output
{
    public function __construct(
        public readonly bool $sent,
        public readonly string $message,
    ) {}
}
