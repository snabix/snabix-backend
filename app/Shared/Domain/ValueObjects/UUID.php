<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Stringable;

readonly class UUID implements Stringable
{
    public string $value;

    public function __construct(
        string $value,
    ) {
        if (!RamseyUuid::isValid($value)) {
            throw new InvalidArgumentException('Некорректный формат UUID');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function generate(): self
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UUID $other): bool
    {
        return $this->value === $other->value;
    }
}
