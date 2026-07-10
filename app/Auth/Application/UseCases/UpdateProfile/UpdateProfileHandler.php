<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\UpdateProfile;

use App\Auth\Application\Services\UserAddressService;
use App\Auth\Application\Services\UserAvatarService;
use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Events\UserEmailVerificationRequested;
use App\Auth\Domain\Events\UserProfileUpdated;
use App\Auth\Domain\ValueObjects\FirstName;
use App\Auth\Domain\ValueObjects\LastName;
use App\Auth\Domain\ValueObjects\PhoneNumber;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\ValueObjects\Email;
use App\Shared\Domain\ValueObjects\UUID;
use Illuminate\Validation\ValidationException;

readonly class UpdateProfileHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAvatarService $userAvatarService,
        private UserAddressService $userAddressService,
    ) {}

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function execute(UpdateProfileInput $data): UpdateProfileOutput
    {
        $userId       = new UUID($data->userId);
        $email        = new Email($data->email);
        $user         = $this->userRepository->byId($userId);

        if ($user === null) {
            throw new NotFoundException('Пользователь не найден.');
        }

        if ($this->userRepository->existByEmailExceptUser($email, $userId)) {
            throw ValidationException::withMessages([
                'email' => ['Пользователь с таким email уже существует.'],
            ]);
        }

        $emailChanged = $user->updateProfile(
            new FirstName($data->firstName),
            new LastName($data->lastName),
            $email,
            filled($data->phoneNumber) ? new PhoneNumber($data->phoneNumber) : null,
            filled($data->description) ? $data->description : null,
            $data->dateOfBirth,
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
            'id'              => $user->id->value(),
            'firstName'       => $user->firstName->value(),
            'lastName'        => $user->lastName->value(),
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
