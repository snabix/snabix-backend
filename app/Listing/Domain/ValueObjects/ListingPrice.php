<?php

declare(strict_types=1);

namespace App\Listing\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class ListingPrice
{
    private function __construct(
        private ?int $amount,
    ) {}

    public static function from(?int $amount): self
    {
        if ($amount !== null && $amount < 0) {
            throw new InvalidArgumentException('Цена не может быть отрицательной.');
        }

        return new self($amount);
    }

    public function value(): ?int
    {
        return $this->amount;
    }
}
