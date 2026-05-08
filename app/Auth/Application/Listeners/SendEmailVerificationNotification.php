<?php

declare(strict_types=1);

namespace App\Auth\Application\Listeners;

use App\Auth\Application\Jobs\SendEmailVerificationJob;
use App\Auth\Domain\Events\UserEmailVerificationRequested;
use App\Auth\Domain\Events\UserRegistered;
use Illuminate\Support\Facades\URL;

readonly class SendEmailVerificationNotification
{
    public function handle(
        UserRegistered | UserEmailVerificationRequested $event,
    ): void {
        $user = $event->user;

        $url = URL::temporarySignedRoute(
            'verify-email',
            now()->addMinutes(60),
            ['user' => $user->id->value()],
        );

        SendEmailVerificationJob::dispatch(
            userId: $user->id->value(),
            email: $user->email->value(),
            name: $user->fullName(),
            verificationUrl: $url,
        );
    }
}
