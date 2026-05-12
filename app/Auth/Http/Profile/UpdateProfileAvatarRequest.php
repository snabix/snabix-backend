<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthUpdateProfileAvatarRequest',
    required: ['avatar'],
    properties: [
        new OA\Property(property: 'avatar', type: 'string', format: 'binary'),
    ],
    type: 'object',
)]
class UpdateProfileAvatarRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'max:3072',
                'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml',
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

        if (! is_object($user)) {
            return '';
        }

        $identifier = $user->getAuthIdentifier();

        return is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';
    }
}
