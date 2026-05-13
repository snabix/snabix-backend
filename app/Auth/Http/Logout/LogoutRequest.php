<?php

declare(strict_types=1);

namespace App\Auth\Http\Logout;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthLogoutRequest',
    type: 'object',
)]
class LogoutRequest extends FormRequest
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

    public function authenticatedUserId(): string
    {
        $user = $this->user();

        if (! $user instanceof EloquentUser) {
            return '';
        }

        $identifier = $user->getAuthIdentifier();

        return is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';
    }
}
