<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\UpdateProfileAvatar;

use App\Shared\Domain\DTO\Input;
use Illuminate\Http\UploadedFile;

class UpdateProfileAvatarInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly UploadedFile $avatar,
    ) {}
}
