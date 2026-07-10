<?php

declare(strict_types=1);

namespace App\Mail\Infrastructure\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** @var array<string, mixed> */
    private array $data;

    /** @var list<array{filename: string, contents: string, mime: string}> */
    private array $attachmentsData;

    /**
     * @param array<string, mixed>                                          $data
     * @param list<array{filename: string, contents: string, mime: string}> $attachments
     */
    public function __construct(
        private readonly string $subjectLine,
        private readonly string $viewName,
        array                   $data = [],
        array                   $attachments = [],
    ) {
        $this->data            = $data;
        $this->attachmentsData = $attachments;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->viewName,
            with: $this->data,
        );
    }

    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        return array_map(
            fn(array $attachment): Attachment => Attachment::fromData(
                fn(): string => $attachment['contents'],
                $attachment['filename'],
            )->withMime($attachment['mime']),
            $this->attachmentsData,
        );
    }
}
