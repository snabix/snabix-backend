<?php

declare(strict_types=1);

namespace App\Auth\Http\TerminateSession;

use App\Auth\Application\UseCases\TerminateSession\TerminateSessionOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TerminateSessionOutput
 */
class TerminateSessionResponse extends JsonResource
{
    /**
     * @return array{terminated: bool}
     */
    public function toArray(Request $request): array
    {
        return [
            'terminated' => $this->terminated,
        ];
    }
}
