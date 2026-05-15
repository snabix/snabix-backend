<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateProfileRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string|Unique>>
     */
    public function rules(): array
    {
        $user       = $this->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';

        return [
            'firstName'   => ['required', 'string', 'max:100'],
            'lastName'    => ['required', 'string', 'max:100'],
            'email'       => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phoneNumber' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
