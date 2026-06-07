<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ChangePassword;

use App\Shared\Domain\DTO\Output;

class ChangePasswordOutput extends Output
{
    public function __construct(
        public readonly bool $changed,
        public readonly string $message,
    ) {}
}
