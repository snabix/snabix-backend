<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

interface SessionAuthenticatorInterface
{
    public function login(string $userId): void;

    public function logout(): void;
}
