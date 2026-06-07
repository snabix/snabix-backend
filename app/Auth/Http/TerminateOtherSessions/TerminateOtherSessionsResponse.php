<?php

declare(strict_types=1);

namespace App\Auth\Http\TerminateOtherSessions;

use App\Auth\Application\UseCases\TerminateOtherSessions\TerminateOtherSessionsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TerminateOtherSessionsOutput
 */
class TerminateOtherSessionsResponse extends JsonResource
{
    /**
     * @return array{terminated: bool, terminatedCount: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'terminated'      => $this->terminated,
            'terminatedCount' => $this->terminatedCount,
        ];
    }
}
