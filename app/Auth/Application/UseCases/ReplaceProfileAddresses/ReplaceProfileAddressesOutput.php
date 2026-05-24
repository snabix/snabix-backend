<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ReplaceProfileAddresses;

use App\Shared\Domain\DTO\Output;

class ReplaceProfileAddressesOutput extends Output
{
    /**
     * @param list<array<string, mixed>> $addresses
     */
    public function __construct(
        public readonly array $addresses,
    ) {}
}
