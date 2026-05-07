<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Listeners;

use App\Shared\Domain\Contracts\LoggableEvent;
use App\Shared\Infrastructure\Services\SystemLogManager;

readonly class PersistLoggableEventListener
{
    public function __construct(
        private SystemLogManager $systemLogManager,
    ) {}

    public function handle(object $event): void
    {
        if (! $event instanceof LoggableEvent) {
            return;
        }

        $this->systemLogManager->log(
            level: $event->logLevel(),
            category: $event->logCategory(),
            message: $event->logMessage(),
            action: $event->logAction(),
            context: $event->logContext(),
            userId: $event->logUserId(),
        );
    }
}
