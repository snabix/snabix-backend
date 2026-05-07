<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\Profile;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\ValueObjects\UUID;

readonly class ProfileHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
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
            'id' => $user->id->value(),
            'name' => $user->name->value(),
            'email' => $user->email->value(),
            'emailVerifiedAt' => $user->emailVerifiedAt?->format(DATE_ATOM),
        ]);
    }
}
