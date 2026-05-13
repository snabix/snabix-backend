<?php

declare(strict_types=1);

namespace App\Auth\Application\Jobs;

use App\Mail\Application\Contracts\MailSender;
use App\Shared\Domain\ValueObjects\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendEmailVerificationJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 60;

    public int $tries   = 3;

    public int $timeout = 30;

    public function __construct(
        public string $userId,
        public string $email,
        public string $name,
        public string $verificationCode,
        public int $expiresInMinutes,
    ) {
        $this->onQueue('notifications');
    }

    public function uniqueId(): string
    {
        return 'email-verification:' . $this->userId;
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(
        MailSender $mailSender,
    ): void {
        $mailSender->send(
            new Email($this->email),
            'Верификация почты',
            'emails.email-verification',
            [
                'expiresInMinutes' => $this->expiresInMinutes,
                'username'         => $this->name,
                'verificationCode' => $this->verificationCode,
            ],
        );
    }
}
