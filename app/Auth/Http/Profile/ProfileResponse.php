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
    ],
    type: 'object',
)]
class ProfileResponse extends JsonResource
{
    /**
     * @return array{id: string, firstName: string, lastName: string, email: string, phoneNumber: ?string, isActive: bool, emailVerifiedAt: ?string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'isActive' => $this->isActive,
            'emailVerifiedAt' => $this->emailVerifiedAt,
        ];
    }
}
