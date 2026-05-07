<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthUpdateProfileRequest',
    required: ['name', 'email'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Imran'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'imran@example.com'),
    ],
    type: 'object',
)]
class UpdateProfileRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string|Unique>>
     */
    public function rules(): array
    {
        $userId = $this->authenticatedUserId();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function authenticatedUserId(): string
    {
        $user = $this->user();

        return is_object($user) ? (string) $user->getAuthIdentifier() : '';
    }
}
