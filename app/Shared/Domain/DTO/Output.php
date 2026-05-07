<?php

declare(strict_types=1);

namespace App\Shared\Domain\DTO;

abstract class Output
{
    /**
     * @param array<string, mixed> $attributes
     */
    public static function from(array $attributes): static
    {
        return new static(...$attributes);
    }
}
