<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exceptions;

use RuntimeException;

final class IdempotencyConflictException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Этот Idempotency-Key уже использован для другого запроса. Создайте новый ключ.',
        );
    }
}
