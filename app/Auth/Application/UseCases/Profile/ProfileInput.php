<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\Profile;

use App\Shared\Domain\DTO\Input;

class ProfileInput extends Input
{
    public function __construct(
        public readonly string $userId,
    ) {}
}
