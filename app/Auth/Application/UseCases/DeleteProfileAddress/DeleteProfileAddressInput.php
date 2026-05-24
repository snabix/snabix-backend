<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\DeleteProfileAddress;

use App\Shared\Domain\DTO\Input;

class DeleteProfileAddressInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly string $addressId,
    ) {}
}
