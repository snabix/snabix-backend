<?php

declare(strict_types=1);

namespace App\Auth\Http\TerminateSession;

use App\Auth\Application\UseCases\TerminateSession\TerminateSessionOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin TerminateSessionOutput
 */
class TerminateSessionResponse extends OutputResource
{
    /**
     * @return array{terminated: bool}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
