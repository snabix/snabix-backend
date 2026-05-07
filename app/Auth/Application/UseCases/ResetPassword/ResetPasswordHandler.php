<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ResetPassword;

use App\Auth\Domain\Events\PasswordResetCompleted;
use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

readonly class ResetPasswordHandler
{
    /**
     * @throws ValidationException
     */
    public function execute(ResetPasswordInput $data): ResetPasswordOutput
    {
        $status = Password::broker('users')->reset(
            [
                'email' => $data->email,
                'token' => $data->token,
                'password' => $data->password,
                'password_confirmation' => $data->password,
            ],
            function (EloquentUser $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                event(new PasswordResetCompleted(
                    userId: (string) $user->getKey(),
                    email: $user->email,
                ));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        return ResetPasswordOutput::from([
            'reset' => true,
            'message' => 'Пароль успешно обновлен.',
        ]);
    }
}
