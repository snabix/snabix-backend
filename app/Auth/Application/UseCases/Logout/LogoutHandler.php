<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\Logout;

use App\Auth\Domain\Events\UserLoggedOut;
use App\Shared\Domain\Contracts\SessionAuthenticatorInterface;

readonly class LogoutHandler
{
    public function __construct(
        private SessionAuthenticatorInterface $sessionAuthenticator,
    ) {}

    public function execute(LogoutInput $data): LogoutOutput
    {
        $this->sessionAuthenticator->logout();

        event(new UserLoggedOut(
            userId: $data->userId,
        ));

        return LogoutOutput::from([
            'loggedOut' => true,
            'message'   => 'Вы успешно вышли из аккаунта.',
        ]);
    }
}
