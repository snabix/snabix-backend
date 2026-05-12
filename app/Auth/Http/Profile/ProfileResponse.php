<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use App\Auth\Application\UseCases\Profile\ProfileOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin ProfileOutput
 */
#[OA\Schema(
    schema: 'AuthProfileResponse',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'firstName', type: 'string', example: 'Imran'),
        new OA\Property(property: 'lastName', type: 'string', example: 'Khan'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'imran@example.com'),
        new OA\Property(property: 'phoneNumber', type: 'string', nullable: true, example: '+79991234567'),
        new OA\Property(property: 'isActive', type: 'boolean', example: true),
        new OA\Property(property: 'emailVerifiedAt', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'avatar',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'url', type: 'string', nullable: true, example: 'http://localhost/storage/images/1/avatar.jpg'),
                new OA\Property(property: 'fileName', type: 'string', example: 'avatar.jpg'),
                new OA\Property(property: 'mimeType', type: 'string', nullable: true, example: 'image/jpeg'),
                new OA\Property(property: 'size', type: 'integer', example: 102400),
                new OA\Property(property: 'humanReadableSize', type: 'string', example: '100 KB'),
            ],
            type: 'object',
            nullable: true,
        ),
    ],
    type: 'object',
)]
class ProfileResponse extends JsonResource
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
     *     avatar: array<string, mixed>|null
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
        ];
    }
}
