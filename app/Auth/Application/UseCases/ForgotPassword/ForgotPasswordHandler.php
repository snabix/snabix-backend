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
        $resetPasswordUrl = config('frontend.reset_password_url');

        $user             = EloquentUser::query()
            ->where('email', $data->email)
            ->first();

        if ($user instanceof EloquentUser && is_string($resetPasswordUrl)) {
            $userId   = $user->getKey();
            $token    = Password::broker('users')->createToken($user);
            $resetUrl = $this->frontendUrlBuilder->build(
                $resetPasswordUrl,
                [
                    'token' => $token,
                    'email' => $user->email,
                ],
            );

            SendPasswordResetJob::dispatch(
                email: $user->email,
                name: $user->account_label,
                resetUrl: $resetUrl,
            );

            event(new PasswordResetRequested(
                userId: is_string($userId) || is_int($userId) ? (string) $userId : '',
                email: $user->email,
            ));
        }

        return ForgotPasswordOutput::from([
            'sent'    => true,
            'message' => 'Если пользователь с таким email существует, инструкция по восстановлению уже отправлена.',
        ]);
    }
}
