<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\TerminateOtherSessions;

use App\Auth\Domain\Contracts\UserSessionRepositoryInterface;

readonly class TerminateOtherSessionsHandler
{
    public function __construct(
        private UserSessionRepositoryInterface $userSessionRepository,
    ) {}

    public function execute(TerminateOtherSessionsInput $data): TerminateOtherSessionsOutput
    {
        $terminatedCount = $this->userSessionRepository->deleteOtherForUser(
            $data->userId,
            $data->currentSessionId,
        );

        return TerminateOtherSessionsOutput::from([
            'terminated'      => true,
            'terminatedCount' => $terminatedCount,
        ]);
    }
}
