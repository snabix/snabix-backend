<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\TerminateSession;

use App\Auth\Domain\Contracts\UserSessionRepositoryInterface;

readonly class TerminateSessionHandler
{
    public function __construct(
        private UserSessionRepositoryInterface $userSessionRepository,
    ) {}

    public function execute(TerminateSessionInput $data): TerminateSessionOutput
    {
        $this->userSessionRepository->deleteForUser(
            $data->userId,
            $data->sessionId,
        );

        return TerminateSessionOutput::from([
            'terminated' => true,
        ]);
    }
}
