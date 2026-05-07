<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthProfileRequest',
    type: 'object',
)]
class ProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
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
