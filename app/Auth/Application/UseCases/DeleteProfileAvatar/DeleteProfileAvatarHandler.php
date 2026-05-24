<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\DeleteProfileAvatar;

use App\Auth\Application\Services\UserAvatarService;
use App\Auth\Application\UseCases\Profile\ProfileHandler;
use App\Auth\Application\UseCases\Profile\ProfileInput;
use App\Auth\Application\UseCases\Profile\ProfileOutput;
use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Events\UserAvatarDeleted;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\ValueObjects\UUID;

readonly class DeleteProfileAvatarHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAvatarService $userAvatarService,
        private ProfileHandler $profileHandler,
    ) {}

    /**
     * @throws NotFoundException
     */
    public function execute(DeleteProfileAvatarInput $data): ProfileOutput
    {
        $user = $this->userRepository->byId(new UUID($data->userId));

        if ($user === null) {
            throw new NotFoundException('Пользователь не найден.');
        }

        $this->userAvatarService->deleteForUser($data->userId);

        event(new UserAvatarDeleted(userId: $data->userId));

        return $this->profileHandler->execute(ProfileInput::from([
            'userId' => $data->userId,
        ]));
    }
}
