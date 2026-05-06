<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthVerifyEmailRequest',
    required: ['user', 'expires', 'signature'],
    properties: [
        new OA\Property(property: 'user', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'expires', type: 'integer', example: 1777027200),
        new OA\Property(property: 'signature', type: 'string', example: 'signed-hash'),
    ],
    type: 'object',
)]
class VerifyEmailRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'user' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
