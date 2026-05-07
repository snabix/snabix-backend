<?php

declare(strict_types=1);

namespace App\Auth\Application\Jobs;

use App\Mail\Application\Contracts\MailSender;
use App\Shared\Domain\ValueObjects\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendPasswordResetJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public string $email,
        public string $name,
        public string $resetUrl,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(MailSender $mailSender): void
    {
        $mailSender->send(
            new Email($this->email),
            'Восстановление пароля',
            'emails.password-reset',
            [
                'username' => $this->name,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }
}
