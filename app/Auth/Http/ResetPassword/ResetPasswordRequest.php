<?php

declare(strict_types=1);

namespace App\Auth\Http\ResetPassword;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthResetPasswordRequest',
    required: ['email', 'token', 'password', 'passwordConfirmation'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'imran@example.com'),
        new OA\Property(property: 'token', type: 'string', example: 'reset-token'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'StrongPassword123!'),
        new OA\Property(property: 'passwordConfirmation', type: 'string', format: 'password', example: 'StrongPassword123!'),
    ],
    type: 'object',
)]
class ResetPasswordRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string|Password>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::default()],
            'passwordConfirmation' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'password_confirmation' => $this->input('passwordConfirmation'),
        ]);
    }
}
