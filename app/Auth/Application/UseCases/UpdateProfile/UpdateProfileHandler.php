<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\UpdateProfile;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Events\UserEmailVerificationRequested;
use App\Auth\Domain\Events\UserProfileUpdated;
use App\Auth\Domain\ValueObjects\Name;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\ValueObjects\Email;
use App\Shared\Domain\ValueObjects\UUID;
use Illuminate\Validation\ValidationException;

readonly class UpdateProfileHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function execute(UpdateProfileInput $data): UpdateProfileOutput
    {
        $userId = new UUID($data->userId);
        $email = new Email($data->email);
        $user = $this->userRepository->byId($userId);

        if ($user === null) {
            throw new NotFoundException('Пользователь не найден.');
        }

        if ($this->userRepository->existByEmailExceptUser($email, $userId)) {
            throw ValidationException::withMessages([
                'email' => ['Пользователь с таким email уже существует.'],
            ]);
        }

        $emailChanged = $user->updateProfile(
            new Name($data->name),
            $email,
        );

        $this->userRepository->save($user);

        event(new UserProfileUpdated(
            user: $user,
            emailChanged: $emailChanged,
        ));

        if ($emailChanged) {
            event(new UserEmailVerificationRequested($user));
        }

        return UpdateProfileOutput::from([
            'id' => $user->id->value(),
            'name' => $user->name->value(),
            'email' => $user->email->value(),
            'emailVerifiedAt' => $user->emailVerifiedAt?->format(DATE_ATOM),
        ]);
    }
}
