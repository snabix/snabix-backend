<?php

declare(strict_types=1);

namespace App\Mail\Infrastructure\Services;

use App\Mail\Application\Contracts\MailSender;
use App\Mail\Infrastructure\Mails\GenericMail;
use App\Shared\Domain\ValueObjects\Email;
use Illuminate\Support\Facades\Mail;

class LaravelMailSender implements MailSender
{
    /**
     * @param array<string, mixed> $data
     */
    public function send(
        Email $to,
        string $subject,
        string $view,
        array $data = [],
    ): void {
        $this->sendWithAttachments($to, $subject, $view, $data);
    }

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
    ): void {
        Mail::to($to->value())
            ->send(
                new GenericMail($subject, $view, $data, $attachments),
            );
    }
}
