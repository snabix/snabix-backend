<?php

declare(strict_types=1);

namespace App\Shared\Application\DTO;

/**
 * @template TValue
 */
final readonly class IdempotencyResult
{
    /**
     * @param TValue $value
     */
    public function __construct(
        public string $resourceId,
        public mixed $value,
    ) {}
}
