<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\EmailVerification;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\ValueObjects\UUID;

readonly class VerifyEmailHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @throws NotFoundException
     */
    public function execute(
        VerifyEmailInput $data,
    ): VerifyEmailOutput {
        $user = $this->userRepository->byId(
            new UUID($data->userId),
        );

        if (is_null($user)) {
            throw new NotFoundException('Пользователь не найден!');
        }

        $user->verifyEmail();

        $this->userRepository->save($user);

        return VerifyEmailOutput::from([
            'verified' => true,
        ]);
    }
}
