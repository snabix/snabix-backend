<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ResetPassword;

use App\Auth\Domain\Contracts\UserSessionRepositoryInterface;
use App\Auth\Domain\Events\PasswordResetCompleted;
use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

readonly class ResetPasswordHandler
{
    public function __construct(
        private UserSessionRepositoryInterface $userSessionRepository,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(ResetPasswordInput $data): ResetPasswordOutput
    {
        $status = DB::transaction(
            fn(): mixed => Password::broker('users')->reset(
                [
                    'email'                 => $data->email,
                    'token'                 => $data->token,
                    'password'              => $data->password,
                    'password_confirmation' => $data->password,
                ],
                function (EloquentUser $user, string $password): void {
                    $userId = $user->getKey();
                    $userId = is_string($userId) || is_int($userId) ? (string) $userId : '';

                    $user->forceFill([
                        'password'       => $password,
                        'remember_token' => Str::random(60),
                    ])->save();

                    $this->userSessionRepository->deleteAllForUser($userId);

                    event(new PasswordReset($user));
                    event(new PasswordResetCompleted(
                        userId: $userId,
                        email: $user->email,
                    ));
                },
            ),
        );

        if ($status !== Password::PASSWORD_RESET) {
            $translationKey = is_string($status) ? $status : 'passwords.reset';

            throw ValidationException::withMessages([
                'email' => [trans($translationKey)],
            ]);
        }

        return ResetPasswordOutput::from([
            'reset'   => true,
            'message' => 'Пароль успешно обновлен.',
        ]);
    }
}
