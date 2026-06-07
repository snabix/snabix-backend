<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\EmailVerification;

use App\Auth\Application\Services\EmailVerificationCodeService;
use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Events\UserEmailVerified;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\ValueObjects\UUID;
use Illuminate\Validation\ValidationException;

readonly class VerifyEmailHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailVerificationCodeService $emailVerificationCodeService,
    ) {}

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function execute(
        VerifyEmailInput $data,
    ): VerifyEmailOutput {
        $user        = $this->userRepository->byId(
            new UUID($data->userId),
        );

        if (is_null($user)) {
            throw new NotFoundException('Пользователь не найден!');
        }

        if ($user->isEmailVerified()) {
            return VerifyEmailOutput::from([
                'verified'        => false,
                'alreadyVerified' => true,
                'message'         => 'Email уже подтвержден.',
            ]);
        }

        $isValidCode = $this->emailVerificationCodeService->verify(
            $user->id->value(),
            $user->email->value(),
            $data->code,
        );

        if (! $isValidCode) {
            throw ValidationException::withMessages([
                'code' => ['Неверный или устаревший код подтверждения.'],
            ]);
        }

        $user->verifyEmail();

        $this->userRepository->save($user);
        $this->emailVerificationCodeService->forget($user->id->value());

        event(new UserEmailVerified($user));

        return VerifyEmailOutput::from([
            'verified'        => true,
            'alreadyVerified' => false,
            'message'         => 'Email успешно подтвержден.',
        ]);
    }
}
