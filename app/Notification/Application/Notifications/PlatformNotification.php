<?php

declare(strict_types=1);

namespace App\Notification\Application\Notifications;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Notification\Application\Services\NotificationPreferenceService;
use App\Notification\Domain\Enums\NotificationEventType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries              = 3;

    /** @var list<string>|null */
    private ?array $forcedChannels = null;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public NotificationEventType $eventType,
        public string $title,
        public string $body,
        public ?string $actionUrl = null,
        public array $context = [],
    ) {
        $this->onQueue('notifications');
    }

    private static function stringContextValue(mixed $value, string $fallback): string
    {
        if (! is_string($value) || $value === '') {
            return $fallback;
        }

        return $value;
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        if ($this->forcedChannels !== null) {
            return $this->forcedChannels;
        }

        if (! $notifiable instanceof EloquentUser) {
            return [];
        }

        $userId = $notifiable->getKey();

        if (! is_string($userId)) {
            return [];
        }

        return app(NotificationPreferenceService::class)->channelsFor(
            $userId,
            $this->eventType,
        );
    }

    /**
     * @param list<string> $channels
     */
    public function forChannels(array $channels): self
    {
        $notification                 = clone $this;
        $notification->forcedChannels = $channels;

        return $notification;
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'eventKey'  => $this->eventType->value,
            'category'  => $this->eventType->category(),
            'title'     => $this->title,
            'body'      => $this->body,
            'actionUrl' => $this->actionUrl,
            'context'   => $this->context === [] ? (object) [] : $this->context,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginDetails = $this->context['loginDetails'] ?? null;

        if ($notifiable instanceof EloquentUser && is_array($loginDetails)) {
            return $this->securityLoginMail($notifiable, $loginDetails);
        }

        $message      = (new MailMessage())
            ->subject($this->title)
            ->greeting('Здравствуйте!')
            ->line($this->body);

        if ($this->actionUrl !== null) {
            $message->action('Открыть SNABIX', $this->actionUrl);
        }

        return $message->line('Вы можете изменить каналы доставки в настройках уведомлений.');
    }

    /**
     * @param array<mixed, mixed> $loginDetails
     */
    private function securityLoginMail(EloquentUser $user, array $loginDetails): MailMessage
    {
        return (new MailMessage())
            ->subject($this->title)
            ->view('emails.security-login', [
                'accountLabel' => $user->account_label,
                'body'         => $this->body,
                'details'      => [
                    'location'   => self::stringContextValue($loginDetails['location'] ?? null, 'неизвестно'),
                    'device'     => self::stringContextValue($loginDetails['device'] ?? null, 'неизвестно'),
                    'browser'    => self::stringContextValue($loginDetails['browser'] ?? null, 'неизвестно'),
                    'ipAddress'  => self::stringContextValue($loginDetails['ipAddress'] ?? null, 'неизвестно'),
                    'signedInAt' => self::stringContextValue($loginDetails['signedInAt'] ?? null, 'неизвестно'),
                ],
                'sessionsUrl'  => $this->frontendUrl('/account/settings/sessions'),
            ]);
    }

    private function frontendUrl(string $path): string
    {
        $frontendUrl = config('frontend.url', 'http://localhost:3000');
        $frontendUrl = is_string($frontendUrl) && $frontendUrl !== '' ? $frontendUrl : 'http://localhost:3000';

        return rtrim($frontendUrl, '/') . '/' . ltrim($path, '/');
    }
}
