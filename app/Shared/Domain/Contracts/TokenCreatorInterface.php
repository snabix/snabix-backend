<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

interface TokenCreatorInterface
{
    public function create(string $userId, string $tokenName): string;
}
