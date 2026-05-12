<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

readonly class LastName implements Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $value       = trim($value);

        if (empty($value)) {
            throw new InvalidArgumentException('Фамилия не может быть пустой.');
        }

        if (mb_strlen($value) < 2) {
            throw new InvalidArgumentException('Фамилия должна содержать минимум 2 символа.');
        }

        if (mb_strlen($value) > 100) {
            throw new InvalidArgumentException('Фамилия не может быть длиннее 100 символов.');
        }

        if (!preg_match('/^[\p{L}\p{M}\s\'-]+$/u', $value)) {
            throw new InvalidArgumentException('Фамилия содержит недопустимые символы.');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
