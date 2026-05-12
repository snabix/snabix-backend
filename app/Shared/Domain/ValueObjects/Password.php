<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

use InvalidArgumentException;

readonly class Password
{
    public string $value;

    public function __construct(
        string $plainPassword,
    ) {
        $plainPassword = trim($plainPassword);

        if (strlen($plainPassword) < 8) {
            throw new InvalidArgumentException('Пароль должен содержать минимум 8 символов');
        }

        if (strlen($plainPassword) > 255) {
            throw new InvalidArgumentException('Пароль слишком длинный (максимум 255 символов)');
        }

        $this->value   = $plainPassword;
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
