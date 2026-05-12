<?php

declare(strict_types=1);

namespace App\Auth\Http\Logout;

use App\Auth\Application\UseCases\Logout\LogoutOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin LogoutOutput
 */
#[OA\Schema(
    schema: 'AuthLogoutResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'loggedOut', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Вы успешно вышли из аккаунта.'),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
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
