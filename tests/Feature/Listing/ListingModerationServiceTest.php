<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Application\Services\ListingModerationService;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Notification\Application\Notifications\PlatformNotification;
use App\Notification\Infrastructure\Models\EloquentNotificationPreference;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\Feature\FeatureTestCase;

class ListingModerationServiceTest extends FeatureTestCase
{
    public function test_moderator_can_publish_listing_and_owner_gets_required_notification(): void
    {
        Notification::fake();
        $user           = EloquentUser::factory()->create();
        $listing        = EloquentListing::factory()->create([
            'user_id' => $user->id,
            'status'  => ListingStatus::PENDING_REVIEW,
        ]);

        EloquentNotificationPreference::query()->create([
            'user_id'       => $user->id,
            'event_key'     => 'listing_moderation',
            'site_enabled'  => false,
            'email_enabled' => false,
        ]);

        $updatedListing = app(ListingModerationService::class)->moderate(
            listing: $listing,
            targetStatus: ListingStatus::PUBLISHED,
            message: 'Спасибо, объявление проверено.',
            adminId: 'admin-1',
        );

        $this->assertSame(ListingStatus::PUBLISHED, $updatedListing->status);
        Notification::assertSentTo(
            $user,
            PlatformNotification::class,
            function (PlatformNotification $notification, array $channels): bool {
                return $notification->eventType->value === 'listing_moderation'
                    && $notification->title === 'Объявление опубликовано'
                    && in_array('database', $channels, true);
            },
        );
        Notification::assertSentTo(
            $user,
            PlatformNotification::class,
            function (PlatformNotification $notification, array $channels): bool {
                return $notification->eventType->value === 'listing_moderation'
                    && $notification->title === 'Объявление опубликовано'
                    && in_array('mail', $channels, true);
            },
        );
    }

    public function test_rejected_listing_requires_message_and_stores_rejection_reason(): void
    {
        Notification::fake();
        $listing = EloquentListing::factory()->create([
            'status' => ListingStatus::PENDING_REVIEW,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(ListingModerationService::class)->moderate(
                listing: $listing,
                targetStatus: ListingStatus::REJECTED,
                message: ' ',
                adminId: 'admin-1',
            );
        } finally {
            $listing->refresh();
            $this->assertSame(ListingStatus::PENDING_REVIEW, $listing->status);
        }
    }

    public function test_moderator_can_reject_listing_with_reason(): void
    {
        Notification::fake();
        $listing        = EloquentListing::factory()->create([
            'status' => ListingStatus::PENDING_REVIEW,
        ]);

        $updatedListing = app(ListingModerationService::class)->moderate(
            listing: $listing,
            targetStatus: ListingStatus::REJECTED,
            message: 'Добавьте реальные фотографии товара.',
            adminId: 'admin-1',
        );

        $this->assertSame(ListingStatus::REJECTED, $updatedListing->status);
        $this->assertSame('Добавьте реальные фотографии товара.', $updatedListing->rejection_reason);
    }
}
