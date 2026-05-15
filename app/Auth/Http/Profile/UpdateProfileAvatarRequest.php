<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use Illuminate\Foundation\Http\FormRequest;

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
}
