<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ForgotPassword;

use App\Auth\Application\Jobs\SendPasswordResetJob;
use App\Auth\Domain\Events\PasswordResetRequested;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Shared\Infrastructure\Services\FrontendUrlBuilder;
use Illuminate\Support\Facades\Password;

readonly class ForgotPasswordHandler
{
    public function __construct(
        private FrontendUrlBuilder $frontendUrlBuilder,
    ) {}

    public function execute(ForgotPasswordInput $data): ForgotPasswordOutput
    {
        $user = EloquentUser::query()
            ->where('email', $data->email)
            ->first();

        if ($user instanceof EloquentUser) {
            $token = Password::broker('users')->createToken($user);
            $resetUrl = $this->frontendUrlBuilder->build(
                (string) config('frontend.reset_password_url'),
                [
                    'token' => $token,
                    'email' => $user->email,
                ],
            );

            SendPasswordResetJob::dispatch(
                email: $user->email,
                name: $user->name,
                resetUrl: $resetUrl,
            );

            event(new PasswordResetRequested(
                userId: (string) $user->getKey(),
                email: $user->email,
            ));
        }

        return ForgotPasswordOutput::from([
            'sent' => true,
            'message' => 'Если пользователь с таким email существует, инструкция по восстановлению уже отправлена.',
        ]);
    }
}
