<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\Logout;

use App\Shared\Domain\DTO\Output;

class LogoutOutput extends Output
{
    public function __construct(
        public readonly bool   $loggedOut,
        public readonly string $message,
    ) {}
}
