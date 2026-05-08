<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

readonly class PhoneNumber implements Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Номер телефона не может быть пустым.');
        }

        $normalized = preg_replace('/[\s\-()]/', '', $value);

        if ($normalized === null || $normalized === '') {
            throw new InvalidArgumentException('Номер телефона содержит недопустимые символы.');
        }

        if (!preg_match('/^\+?[0-9]{10,15}$/', $normalized)) {
            throw new InvalidArgumentException('Номер телефона должен содержать от 10 до 15 цифр.');
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
