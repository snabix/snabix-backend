<?php

declare(strict_types=1);

namespace App\Auth\Http\SignUp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthSignUpRequest',
    required: ['firstName', 'lastName', 'phoneNumber', 'email', 'password', 'passwordConfirmation'],
    properties: [
        new OA\Property(property: 'firstName', type: 'string', example: 'Imran'),
        new OA\Property(property: 'lastName', type: 'string', example: 'Khan'),
        new OA\Property(property: 'phoneNumber', type: 'string', example: '+79991234567'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'imran@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'StrongPassword123!'),
        new OA\Property(property: 'passwordConfirmation', type: 'string', format: 'password', example: 'StrongPassword123!'),
    ],
    type: 'object',
)]
class SignUpRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string|Password>>
     */
    public function rules(): array
    {
        return [
            'firstName'            => ['required', 'string', 'max:100'],
            'lastName'             => ['required', 'string', 'max:100'],
            'phoneNumber'          => ['required', 'string', 'max:20'],
            'email'                => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'             => ['required', 'confirmed', Password::default()],
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
