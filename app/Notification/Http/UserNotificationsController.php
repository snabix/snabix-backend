<?php

declare(strict_types=1);

namespace App\Notification\Http;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class UserNotificationsController
{
    /**
     * @return array<string, mixed>
     */
    private static function payload(DatabaseNotification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return [
            'id'        => $notification->id,
            'eventKey'  => self::stringValue($data['eventKey'] ?? null, 'legacy_notification'),
            'category'  => self::categoryValue($data['category'] ?? null),
            'title'     => self::stringValue($data['title'] ?? null, 'Уведомление'),
            'body'      => self::stringValue($data['body'] ?? null, 'Откройте уведомление, чтобы посмотреть детали.'),
            'actionUrl' => self::nullableStringValue($data['actionUrl'] ?? null),
            'context'   => self::contextValue($data['context'] ?? null),
            'isRead'    => $notification->read_at !== null,
            'createdAt' => $notification->created_at?->toAtomString(),
            'readAt'    => $notification->read_at?->toAtomString(),
        ];
    }

    private static function stringValue(mixed $value, string $fallback): string
    {
        return is_scalar($value) && (string) $value !== '' ? (string) $value : $fallback;
    }

    private static function nullableStringValue(mixed $value): ?string
    {
        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }

    private static function categoryValue(mixed $value): string
    {
        return in_array($value, ['listings', 'messages', 'activity', 'system'], true)
            ? $value
            : 'system';
    }

    /**
     * @return array<string, mixed>
     */
    private static function contextValue(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    public function index(Request $request): JsonResponse
    {
        $user      = $this->user($request);
        $paginator = $user->notifications()->latest()->paginate(
            perPage: min(max($request->integer('perPage', 20), 1), 50),
        );

        return response()->json(['data' => [
            'items'       => $paginator->getCollection()->map(self::payload(...))->values(),
            'unreadCount' => $user->unreadNotifications()->count(),
            'meta'        => [
                'currentPage' => $paginator->currentPage(),
                'lastPage'    => $paginator->lastPage(),
                'perPage'     => $paginator->perPage(),
                'total'       => $paginator->total(),
            ],
        ]]);
    }

    public function markRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = $this->user($request)->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        $notification->refresh();

        return response()->json(['data' => self::payload($notification)]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->user($request)->unreadNotifications->markAsRead();

        return response()->json(['data' => ['markedRead' => true]]);
    }

    private function user(Request $request): EloquentUser
    {
        $user = $request->user();

        abort_unless($user instanceof EloquentUser, 401);

        return $user;
    }
}
