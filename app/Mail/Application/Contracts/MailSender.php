<?php

declare(strict_types=1);

namespace App\Mail\Application\Contracts;

use App\Shared\Domain\ValueObjects\Email;

interface MailSender
{
    /**
     * @param array<string, mixed> $data
     */
    public function send(
        Email $to,
        string $subject,
        string $view,
        array $data = [],
    ): void;
}
