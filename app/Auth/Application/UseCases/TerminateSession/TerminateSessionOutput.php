<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\TerminateSession;

use Spatie\LaravelData\Data;

class TerminateSessionOutput extends Data
{
    public function __construct(
        public bool $terminated,
    ) {}
}
