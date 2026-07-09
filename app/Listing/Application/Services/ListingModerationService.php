<?php

declare(strict_types=1);

namespace App\Listing\Application\Services;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Domain\Contracts\ListingWriterInterface;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Notification\Application\Notifications\PlatformNotification;
use App\Notification\Application\Services\PlatformNotificationDispatcher;
use App\Notification\Domain\Enums\NotificationEventType;
use Illuminate\Validation\ValidationException;
use Throwable;

final readonly class ListingModerationService
{
    public function __construct(
        private ListingWriterInterface $listingWriter,
        private PlatformNotificationDispatcher $notificationDispatcher,
    ) {}

    /**
     * @throws Throwable
     */
    public function moderate(
        EloquentListing $listing,
        ListingStatus $targetStatus,
        ?string $message = null,
        ?string $adminId = null,
    ): EloquentListing {
        $message        = $this->normalizeMessage($message);
        $previousStatus = $listing->status;

        if ($targetStatus === ListingStatus::REJECTED && $message === null) {
            throw ValidationException::withMessages([
                'message' => ['Укажите причину отклонения объявления.'],
            ]);
        }

        if ($previousStatus === $targetStatus) {
            return $listing;
        }

        $updatedListing = $this->listingWriter->transitionStatus(
            listing: $listing,
            status: $targetStatus,
            rejectionReason: $targetStatus === ListingStatus::REJECTED ? $message : null,
        );

        $this->notifyOwner(
            listing: $updatedListing,
            previousStatus: $previousStatus,
            targetStatus: $targetStatus,
            message: $message,
            adminId: $adminId,
        );

        return $updatedListing;
    }

    private function normalizeMessage(?string $message): ?string
    {
        if ($message === null) {
            return null;
        }

        $message = trim($message);

        return $message === '' ? null : $message;
    }

    private function notifyOwner(
        EloquentListing $listing,
        ListingStatus $previousStatus,
        ListingStatus $targetStatus,
        ?string $message,
        ?string $adminId,
    ): void {
        $user         = $listing->user;

        if (! $user instanceof EloquentUser) {
            return;
        }

        $notification = new PlatformNotification(
            eventType: NotificationEventType::LISTING_MODERATION,
            title: $this->notificationTitle($targetStatus),
            body: $this->notificationBody($listing, $targetStatus, $message),
            actionUrl: $this->listingActionUrl($listing),
            context: [
                'listingId'      => $listing->id,
                'listingTitle'   => $listing->title,
                'previousStatus' => $previousStatus->value,
                'newStatus'      => $targetStatus->value,
                'adminId'        => $adminId,
                'message'        => $message,
            ],
        );

        $this->notificationDispatcher->dispatch($user, $notification);
    }

    private function notificationTitle(ListingStatus $status): string
    {
        return match ($status) {
            ListingStatus::PUBLISHED      => 'Объявление опубликовано',
            ListingStatus::REJECTED       => 'Объявление отклонено',
            ListingStatus::ARCHIVED       => 'Объявление перенесено в архив',
            ListingStatus::PENDING_REVIEW => 'Объявление отправлено на проверку',
            ListingStatus::DRAFT          => 'Объявление возвращено в черновики',
        };
    }

    private function notificationBody(
        EloquentListing $listing,
        ListingStatus $status,
        ?string $message,
    ): string {
        $defaultBody = match ($status) {
            ListingStatus::PUBLISHED      => sprintf('Ваше объявление "%s" опубликовано и доступно на площадке.', $listing->title),
            ListingStatus::REJECTED       => sprintf('Ваше объявление "%s" не прошло модерацию.', $listing->title),
            ListingStatus::ARCHIVED       => sprintf('Ваше объявление "%s" перенесено в архив.', $listing->title),
            ListingStatus::PENDING_REVIEW => sprintf('Ваше объявление "%s" снова находится на проверке.', $listing->title),
            ListingStatus::DRAFT          => sprintf('Ваше объявление "%s" возвращено в черновики.', $listing->title),
        };

        return $message !== null
            ? $defaultBody . ' Комментарий модератора: ' . $message
            : $defaultBody;
    }

    private function listingActionUrl(EloquentListing $listing): string
    {
        $frontendUrl = config('frontend.url', 'http://localhost:3000');
        $frontendUrl = is_string($frontendUrl) ? rtrim($frontendUrl, '/') : 'http://localhost:3000';

        return $frontendUrl . '/account/listings/' . $listing->id;
    }
}
