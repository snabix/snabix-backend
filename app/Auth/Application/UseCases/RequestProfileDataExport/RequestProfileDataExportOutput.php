<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\RequestProfileDataExport;

use App\Shared\Domain\DTO\Output;

class RequestProfileDataExportOutput extends Output
{
    public function __construct(
        public readonly bool $requested,
        public readonly string $message,
    ) {}
}
