<?php

declare(strict_types=1);

namespace App\Auth\Http\ForgotPassword;

use Illuminate\Foundation\Http\FormRequest;

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

    /**
     * @return array{email: string}
     */
    public function inputData(): array
    {
        return [
            'email' => $this->string('email')->toString(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
