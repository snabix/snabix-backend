<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ResendEmailVerification;

use App\Shared\Domain\DTO\Output;

class ResendEmailVerificationOutput extends Output
{
    public function __construct(
        public readonly bool $sent,
        public readonly string $message,
        public readonly int $cooldownSeconds,
    ) {}
}
