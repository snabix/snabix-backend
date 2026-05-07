<?php

declare(strict_types=1);

namespace App\Auth\Http\ForgotPassword;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthForgotPasswordRequest',
    required: ['email'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'imran@example.com'),
    ],
    type: 'object',
)]
class ForgotPasswordRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
