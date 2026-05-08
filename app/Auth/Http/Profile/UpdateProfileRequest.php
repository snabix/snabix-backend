<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthUpdateProfileRequest',
    required: ['firstName', 'lastName', 'email'],
    properties: [
        new OA\Property(property: 'firstName', type: 'string', example: 'Imran'),
        new OA\Property(property: 'lastName', type: 'string', example: 'Khan'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'imran@example.com'),
        new OA\Property(property: 'phoneNumber', type: 'string', nullable: true, example: '+79991234567'),
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
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'email' => [
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

    public function authenticatedUserId(): string
    {
        $user = $this->user();

        return is_object($user) ? (string) $user->getAuthIdentifier() : '';
    }
}
