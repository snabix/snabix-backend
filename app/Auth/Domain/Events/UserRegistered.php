<?php

declare(strict_types=1);

namespace App\Auth\Domain\Events;

use App\Auth\Domain\Entities\User;

readonly class UserRegistered
{
    public function __construct(
        public User $user,
    ) {}
}
