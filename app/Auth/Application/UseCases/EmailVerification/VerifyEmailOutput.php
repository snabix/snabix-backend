<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\EmailVerification;

use App\Shared\Domain\DTO\Output;

class VerifyEmailOutput extends Output
{
    public function __construct(
        public readonly bool $verified,
    ) {}
}
