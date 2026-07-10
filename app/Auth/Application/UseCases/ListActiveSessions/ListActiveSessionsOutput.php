<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ListActiveSessions;

use Spatie\LaravelData\Data;

class ListActiveSessionsOutput extends Data
{
    /**
     * @param list<array{
     *     id: string,
     *     deviceName: string,
     *     browser: string,
     *     ipAddress: ?string,
     *     locationLabel: string,
     *     type: string,
     *     isCurrent: bool,
     *     lastActivityAt: ?string
     * }> $items
     */
    public function __construct(
        public array $items,
    ) {}
}
