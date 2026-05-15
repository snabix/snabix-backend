<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'digits:6'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
