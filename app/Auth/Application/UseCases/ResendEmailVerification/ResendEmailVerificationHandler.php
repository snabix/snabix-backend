<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ResendEmailVerification;

use App\Auth\Application\Services\EmailVerificationCodeService;
use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Events\UserEmailVerificationRequested;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Domain\ValueObjects\UUID;

readonly class ResendEmailVerificationHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailVerificationCodeService $emailVerificationCodeService,
    ) {}

    /**
     * @throws NotFoundException
     */
    public function execute(ResendEmailVerificationInput $data): ResendEmailVerificationOutput
    {
        $user = $this->userRepository->byId(
            new UUID($data->userId),
        );

        if ($user === null) {
            throw new NotFoundException('Пользователь не найден.');
        }

        if ($user->isEmailVerified()) {
            return ResendEmailVerificationOutput::from([
                'sent'            => false,
                'message'         => 'Email уже подтвержден.',
                'cooldownSeconds' => 0,
            ]);
        }

        $cooldownSeconds = $this->emailVerificationCodeService->resendCooldownSeconds(
            $user->id->value(),
        );

        if ($cooldownSeconds > 0) {
            return ResendEmailVerificationOutput::from([
                'sent'            => false,
                'message'         => 'Новый код пока запрашивать рано. Попробуйте чуть позже.',
                'cooldownSeconds' => $cooldownSeconds,
            ]);
        }

        event(new UserEmailVerificationRequested($user));

        return ResendEmailVerificationOutput::from([
            'sent'            => true,
            'message'         => 'Код подтверждения отправлен повторно.',
            'cooldownSeconds' => $this->emailVerificationCodeService->resendCooldownSecondsValue(),
        ]);
    }
}
