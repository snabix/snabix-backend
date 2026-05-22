<?php

declare(strict_types=1);

namespace App\Auth\Http\UpdateProfileAvatar;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UpdateProfileAvatarRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

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

    /**
     * @return array{userId: string, avatar: UploadedFile|null}
     */
    public function inputData(): array
    {
        return [
            'userId' => $this->userId(),
            'avatar' => $this->file('avatar'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
