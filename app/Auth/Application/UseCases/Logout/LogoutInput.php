<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\Logout;

use App\Shared\Domain\DTO\Input;

class LogoutInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly ?int $tokenId,
    ) {}
}
