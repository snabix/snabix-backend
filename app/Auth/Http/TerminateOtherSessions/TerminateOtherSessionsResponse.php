<?php

declare(strict_types=1);

namespace App\Auth\Http\TerminateOtherSessions;

use App\Auth\Application\UseCases\TerminateOtherSessions\TerminateOtherSessionsOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin TerminateOtherSessionsOutput
 */
class TerminateOtherSessionsResponse extends OutputResource
{
    /**
     * @return array{terminated: bool, terminatedCount: int}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
