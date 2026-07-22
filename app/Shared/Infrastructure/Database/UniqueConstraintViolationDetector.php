<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database;

use Illuminate\Database\UniqueConstraintViolationException;

final readonly class UniqueConstraintViolationDetector
{
    public function matches(
        UniqueConstraintViolationException $exception,
        string $constraintName,
    ): bool {
        return str_contains(
            $exception->getMessage(),
            sprintf('"%s"', $constraintName),
        );
    }
}
