<?php

declare(strict_types=1);

namespace App\Auth\Http\SignIn;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthSignInRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'imran@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'StrongPassword123!'),
    ],
    type: 'object',
)]
class SignInRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authorize(): true
    {
        return true;
    }
}
