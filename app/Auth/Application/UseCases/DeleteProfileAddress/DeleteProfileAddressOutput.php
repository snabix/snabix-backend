<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\DeleteProfileAddress;

use App\Shared\Domain\DTO\Output;

class DeleteProfileAddressOutput extends Output
{
    public function __construct(
        public readonly bool $deleted,
    ) {}
}
