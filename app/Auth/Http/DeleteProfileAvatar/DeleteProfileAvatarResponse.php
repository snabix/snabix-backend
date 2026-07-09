<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAvatar;

use App\Auth\Application\UseCases\Profile\ProfileOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ProfileOutput
 */
class DeleteProfileAvatarResponse extends OutputResource
{
    /**
     * @return array{
     *     id: string,
     *     firstName: string,
     *     lastName: string,
     *     email: string,
     *     phoneNumber: string|null,
     *     aboutMe: string|null,
     *     isActive: bool,
     *     emailVerifiedAt: string|null,
     *     avatar: array<string, mixed>|null,
     *     addresses: list<array<string, mixed>>
     * }
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
