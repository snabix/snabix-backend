<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\RequestProfileDataExport;

use App\Shared\Domain\DTO\Input;

class RequestProfileDataExportInput extends Input
{
    public function __construct(
        public readonly string $userId,
    ) {}
}
