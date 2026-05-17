<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use Illuminate\Foundation\Http\FormRequest;

class ResendEmailVerificationRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }
}
