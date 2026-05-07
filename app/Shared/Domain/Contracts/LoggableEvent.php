<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

use App\Shared\Domain\Enums\SystemLogLevel;

interface LoggableEvent
{
    public function logLevel(): SystemLogLevel;

    public function logCategory(): string;

    public function logMessage(): string;

    public function logAction(): ?string;

    /**
     * @return array<string, mixed>|null
     */
    public function logContext(): ?array;

    public function logUserId(): ?string;
}
