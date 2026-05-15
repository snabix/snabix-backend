<?php

declare(strict_types=1);

namespace App\Auth\Http\SignIn;

use Illuminate\Foundation\Http\FormRequest;

class SignInRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authorize(): true
    {
        return true;
    }
}
