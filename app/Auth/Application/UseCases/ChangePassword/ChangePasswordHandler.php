<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ChangePassword;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Contracts\UserSessionRepositoryInterface;
use App\Auth\Domain\Events\UserPasswordChanged;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\Contracts\HasherInterface;
use App\Shared\Domain\Contracts\SessionAuthenticatorInterface;
use App\Shared\Domain\ValueObjects\Password;
use App\Shared\Domain\ValueObjects\UUID;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

readonly class ChangePasswordHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserSessionRepositoryInterface $userSessionRepository,
        private HasherInterface $hasherService,
        private SessionAuthenticatorInterface $sessionAuthenticator,
    ) {}

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function execute(ChangePasswordInput $data): ChangePasswordOutput
    {
        $user             = $this->userRepository->byId(new UUID($data->userId));

        if ($user === null) {
            throw new NotFoundException('Пользователь не найден.');
        }

        if (! $this->hasherService->verify($data->currentPassword, $user->password->value())) {
            throw ValidationException::withMessages([
                'currentPassword' => ['Текущий пароль указан неверно.'],
            ]);
        }

        $currentSessionId = $this->sessionAuthenticator->regenerate();

        DB::transaction(function () use ($data, $user, $currentSessionId): void {
            $user->changePassword(new Password(
                $this->hasherService->hash($data->password),
            ));

            $this->userRepository->save($user);
            $this->userSessionRepository->deleteOtherForUser(
                $data->userId,
                $currentSessionId,
            );
        });

        event(new UserPasswordChanged($user));

        return ChangePasswordOutput::from([
            'changed' => true,
            'message' => 'Пароль успешно изменен.',
        ]);
    }
}
