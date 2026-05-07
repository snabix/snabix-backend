<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\SignUp;

use App\Shared\Domain\DTO\Output;

class SignUpOutput extends Output
{
    public function __construct(
        public string $token,
    ) {}
}
