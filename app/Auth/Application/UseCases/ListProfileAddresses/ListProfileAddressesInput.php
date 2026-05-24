<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ListProfileAddresses;

use App\Shared\Domain\DTO\Input;

class ListProfileAddressesInput extends Input
{
    public function __construct(
        public readonly string $userId,
    ) {}
}
