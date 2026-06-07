<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\DeleteProfileAvatar;

use App\Shared\Domain\DTO\Input;

class DeleteProfileAvatarInput extends Input
{
    public function __construct(
        public readonly string $userId,
    ) {}
}
