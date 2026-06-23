<?php

declare(strict_types=1);

namespace App\Auth\Application\Listeners;

use App\Auth\Application\Jobs\SendEmailVerificationJob;
use App\Auth\Application\Services\EmailVerificationCodeService;
use App\Auth\Domain\Events\UserEmailVerificationRequested;
use App\Auth\Domain\Events\UserRegistered;

readonly class SendEmailVerificationNotification
{
    public function __construct(
        private EmailVerificationCodeService $emailVerificationCodeService,
    ) {}

    public function handle(
        UserRegistered | UserEmailVerificationRequested $event,
    ): void {
        $user = $event->user;
        $code = $event instanceof UserEmailVerificationRequested
            ? $this->emailVerificationCodeService->reuseOrIssue(
                $user->id->value(),
                $user->email->value(),
            )
            : $this->emailVerificationCodeService->issue(
                $user->id->value(),
                $user->email->value(),
            );

        SendEmailVerificationJob::dispatch(
            userId: $user->id->value(),
            email: $user->email->value(),
            name: $user->fullName(),
            verificationCode: $code,
            expiresInMinutes: $this->emailVerificationCodeService->expiresInMinutes(),
        );
    }
}
