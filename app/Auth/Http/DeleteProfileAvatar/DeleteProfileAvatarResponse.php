<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAvatar;

use App\Auth\Application\UseCases\Profile\ProfileOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProfileOutput
 */
class DeleteProfileAvatarResponse extends JsonResource
{
    /**
     * @return array{
     *     id: string,
     *     firstName: string,
     *     lastName: string,
     *     email: string,
     *     phoneNumber: ?string,
     *     isActive: bool,
     *     emailVerifiedAt: ?string,
     *     avatar: array<string, mixed>|null,
     *     addresses: list<array<string, mixed>>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'firstName'       => $this->firstName,
            'lastName'        => $this->lastName,
            'email'           => $this->email,
            'phoneNumber'     => $this->phoneNumber,
            'isActive'        => $this->isActive,
            'emailVerifiedAt' => $this->emailVerifiedAt,
            'avatar'          => $this->avatar,
            'addresses'       => $this->addresses,
        ];
    }
}
