<?php

declare(strict_types=1);

namespace App\Auth\Http\ResetPassword;

use App\Auth\Application\UseCases\ResetPassword\ResetPasswordOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin ResetPasswordOutput
 */
#[OA\Schema(
    schema: 'AuthResetPasswordResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'reset', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Пароль успешно обновлен.'),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
class ResetPasswordResponse extends JsonResource
{
    /**
     * @return array{reset: bool, message: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'reset' => $this->reset,
            'message' => $this->message,
        ];
    }
}
