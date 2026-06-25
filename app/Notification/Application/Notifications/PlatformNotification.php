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

    public int $tries = 3;

    /** @var list<string>|null */
    private ?array $forcedChannels = null;

    /**
     * @param  array<string, mixed>  $context
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
     * @param  list<string>  $channels
     */
    public function forChannels(array $channels): self
    {
        $notification = clone $this;
        $notification->forcedChannels = $channels;

        return $notification;
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'eventKey' => $this->eventType->value,
            'category' => $this->eventType->category(),
            'title' => $this->title,
            'body' => $this->body,
            'actionUrl' => $this->actionUrl,
            'context' => $this->context === [] ? (object) [] : $this->context,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->title)
            ->greeting('Здравствуйте!')
            ->line($this->body);

        if ($this->actionUrl !== null) {
            $message->action('Открыть SNABIX', $this->actionUrl);
        }

        return $message->line('Вы можете изменить каналы доставки в настройках уведомлений.');
    }
}
