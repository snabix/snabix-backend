<?php

declare(strict_types=1);

namespace App\Auth\Http\SignUp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

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
            'phoneNumber'          => ['nullable', 'string', 'max:20'],
            'email'                => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'             => ['required', 'confirmed', Password::default()],
            'passwordConfirmation' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array{firstName: string, lastName: string, phoneNumber: ?string, email: string, password: string, passwordConfirmation: string}
     */
    public function inputData(): array
    {
        return [
            'firstName'            => $this->string('firstName')->toString(),
            'lastName'             => $this->string('lastName')->toString(),
            'phoneNumber'          => $this->filled('phoneNumber')
                ? $this->string('phoneNumber')->toString()
                : null,
            'email'                => $this->string('email')->toString(),
            'password'             => $this->string('password')->toString(),
            'passwordConfirmation' => $this->string('passwordConfirmation')->toString(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'password_confirmation' => $this->input('passwordConfirmation'),
        ]);
    }
}
