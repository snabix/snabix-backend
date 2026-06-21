<?php

declare(strict_types=1);

namespace App\Listing\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class ListingCurrency
{
    private const string DEFAULT_CODE = 'RUB';

    private function __construct(
        private string $code,
    ) {}

    public static function from(?string $code): self
    {
        $normalized = mb_strtoupper(trim($code ?? ''));

        if ($normalized === '') {
            return new self(self::DEFAULT_CODE);
        }

        if (mb_strlen($normalized) !== 3) {
            throw new InvalidArgumentException('Код валюты должен состоять из трёх символов.');
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->code;
    }
}
