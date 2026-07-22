<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\Profile;

use App\Auth\Application\Services\UserAddressService;
use App\Auth\Application\Services\UserAvatarService;
use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\ValueObjects\UUID;

readonly class ProfileHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAvatarService $userAvatarService,
        private UserAddressService $userAddressService,
    ) {}

    /**
     * @throws NotFoundException
     */
    public function execute(ProfileInput $data): ProfileOutput
    {
        $user = $this->userRepository->byId(
            new UUID($data->userId),
        );

        if ($user === null) {
            throw new NotFoundException('Пользователь не найден.');
        }

        return ProfileOutput::from([
            'id'              => $user->id->value(),
            'firstName'       => $user->firstName?->value(),
            'lastName'        => $user->lastName?->value(),
            'email'           => $user->email->value(),
            'phoneNumber'     => $user->phoneNumber?->value(),
            'description'     => $user->description,
            'dateOfBirth'     => $user->dateOfBirth,
            'isActive'        => $user->isActive(),
            'emailVerifiedAt' => $user->emailVerifiedAt?->format(DATE_ATOM),
            'avatar'          => $this->userAvatarService->toPayload($data->userId),
            'addresses'       => $this->userAddressService->listPayload($data->userId),
        ]);
    }
}
