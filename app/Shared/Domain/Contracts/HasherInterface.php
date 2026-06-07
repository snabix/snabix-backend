<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

interface HasherInterface
{
    public function hash(string $plain): string;

    public function verify(string $plain, string $hashed): bool;
}
