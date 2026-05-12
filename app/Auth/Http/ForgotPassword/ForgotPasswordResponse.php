<?php

declare(strict_types=1);

namespace App\Auth\Http\ForgotPassword;

use App\Auth\Application\UseCases\ForgotPassword\ForgotPasswordOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin ForgotPasswordOutput
 */
#[OA\Schema(
    schema: 'AuthForgotPasswordResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'sent', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Если пользователь с таким email существует, инструкция уже отправлена.'),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
class ForgotPasswordResponse extends JsonResource
{
    /**
     * @return array{sent: bool, message: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'sent'    => $this->sent,
            'message' => $this->message,
        ];
    }
}
