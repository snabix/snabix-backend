<?php

declare(strict_types=1);

namespace App\Auth\Http\ChangePassword;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, string|Password>>
     */
    public function rules(): array
    {
        return [
            'currentPassword'      => ['required', 'string'],
            'password'             => ['required', 'confirmed', Password::default()],
            'passwordConfirmation' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function inputData(): array
    {
        return [
            'userId'          => $this->userId(),
            'currentPassword' => $this->string('currentPassword')->toString(),
            'password'        => $this->string('password')->toString(),
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
