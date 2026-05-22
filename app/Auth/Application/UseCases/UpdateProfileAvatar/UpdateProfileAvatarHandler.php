<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\UpdateProfileAvatar;

use App\Auth\Application\Services\UserAvatarService;
use App\Auth\Application\UseCases\Profile\ProfileHandler;
use App\Auth\Application\UseCases\Profile\ProfileInput;
use App\Auth\Application\UseCases\Profile\ProfileOutput;
use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Events\UserAvatarUpdated;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\ValueObjects\UUID;
use Throwable;

readonly class UpdateProfileAvatarHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAvatarService $userAvatarService,
        private ProfileHandler $profileHandler,
    ) {}

    /**
     * @throws NotFoundException
     * @throws Throwable
     */
    public function execute(UpdateProfileAvatarInput $data): ProfileOutput
    {
        $user   = $this->userRepository->byId(new UUID($data->userId));

        if ($user === null) {
            throw new NotFoundException('Пользователь не найден.');
        }

        $avatar = $this->userAvatarService->uploadForUser($data->userId, $data->avatar);

        event(new UserAvatarUpdated(
            userId: $data->userId,
            mediaId: $avatar->id,
        ));

        return $this->profileHandler->execute(ProfileInput::from([
            'userId' => $data->userId,
        ]));
    }
}
