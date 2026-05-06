<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

use PharIo\Manifest\InvalidEmailException;

readonly class Email
{
    public string $value;

    public function __construct(
        string $value,
    ) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException(
                'Некорректный адрес электронной почты!',
            );
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
