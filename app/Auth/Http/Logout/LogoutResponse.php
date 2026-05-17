<?php

declare(strict_types=1);

namespace App\Auth\Http\Logout;

use App\Auth\Application\UseCases\Logout\LogoutOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LogoutOutput
 */
class LogoutResponse extends JsonResource
{
    /**
     * @return array{loggedOut: bool, message: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'loggedOut' => $this->loggedOut,
            'message'   => $this->message,
        ];
    }
}
