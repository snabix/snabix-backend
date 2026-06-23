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
        return [
            'id'        => $notification->id,
            ...$notification->data,
            'isRead'    => $notification->read_at !== null,
            'createdAt' => $notification->created_at?->toAtomString(),
            'readAt'    => $notification->read_at?->toAtomString(),
        ];
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
