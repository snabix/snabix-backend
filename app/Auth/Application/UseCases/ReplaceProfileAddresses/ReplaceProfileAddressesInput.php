<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ReplaceProfileAddresses;

use App\Shared\Domain\DTO\Input;

class ReplaceProfileAddressesInput extends Input
{
    /**
     * @param list<array<array-key, mixed>> $addresses
     */
    public function __construct(
        public readonly string $userId,
        public readonly array $addresses,
    ) {}
}
