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

    /**
     * @param array<string, mixed>                                          $data
     * @param list<array{filename: string, contents: string, mime: string}> $attachments
     */
    public function sendWithAttachments(
        Email $to,
        string $subject,
        string $view,
        array $data = [],
        array $attachments = [],
    ): void;
}
