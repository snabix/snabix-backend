<?php

declare(strict_types=1);

namespace App\Auth\Http\ResetPassword;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string|Password>>
     */
    public function rules(): array
    {
        return [
            'email'                => ['required', 'email'],
            'token'                => ['required', 'string'],
            'password'             => ['required', 'confirmed', Password::default()],
            'passwordConfirmation' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array{email: string, token: string, password: string}
     */
    public function inputData(): array
    {
        return [
            'email'    => $this->string('email')->toString(),
            'token'    => $this->string('token')->toString(),
            'password' => $this->string('password')->toString(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'password_confirmation' => $this->input('passwordConfirmation'),
        ]);
    }
}
