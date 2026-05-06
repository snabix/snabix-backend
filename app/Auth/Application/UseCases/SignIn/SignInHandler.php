<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\SignIn;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Shared\Domain\Contracts\HasherInterface;
use App\Shared\Domain\Contracts\TokenCreatorInterface;
use App\Shared\Domain\ValueObjects\Email;
use Illuminate\Validation\ValidationException;

readonly class SignInHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private HasherInterface $hasherService,
        private TokenCreatorInterface $tokenCreator,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(
        SignInInput $data,
    ): SignInOutput {
        $user = $this->userRepository->byEmail(
            new Email($data->email),
        );

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Неверный логин или пароль.'],
            ]);
        }

        $isValidPassword = $this->hasherService->verify(
            plain: $data->password,
            hashed: $user->password->value(),
        );

        if (!$isValidPassword) {
            throw ValidationException::withMessages([
                'email' => ['Неверный логин или пароль.'],
            ]);
        }

        $token = $this->tokenCreator->create(
            userId: $user->id->value(),
            tokenName: 'auth_token',
        );

        return SignInOutput::from([
            'token' => $token,
            'userId' => $user->id->value(),
        ]);
    }
}
