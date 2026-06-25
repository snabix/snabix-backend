<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Notification\Application\Notifications\PlatformNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\FeatureTestCase;

class NotificationApiTest extends FeatureTestCase
{
    public function test_user_can_read_and_update_delivery_preferences(): void
    {
        $user     = EloquentUser::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/notifications/preferences')
            ->assertOk()
            ->assertJsonCount(10, 'data.items');

        $items    = $response->json('data.items');
        $security = collect($items)->firstWhere('key', 'security_login');

        $this->assertTrue($security['siteEnabled']);

        $this->putJson('/api/v1/notifications/preferences', [
            'items' => [[
                'key'          => 'security_login',
                'siteEnabled'  => false,
                'emailEnabled' => false,
            ]],
        ])->assertOk()
            ->assertJsonPath('data.items.7.siteEnabled', true)
            ->assertJsonPath('data.items.7.emailEnabled', false);
    }

    public function test_user_can_list_and_mark_notifications_as_read(): void
    {
        $user         = EloquentUser::factory()->create();
        Sanctum::actingAs($user);
        $notification = new PlatformNotification(
            eventType: \App\Notification\Domain\Enums\NotificationEventType::PRICE_CHANGES,
            title: 'Цена изменилась',
            body: 'Цена объявления снижена.',
            actionUrl: '/listings/example',
        );

        $user->notifyNow($notification, ['database']);

        $id           = $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('data.unreadCount', 1)
            ->assertJsonPath('data.items.0.title', 'Цена изменилась')
            ->json('data.items.0.id');

        $this->patchJson("/api/v1/notifications/{$id}/read")
            ->assertOk()
            ->assertJsonPath('data.isRead', true);

        $this->getJson('/api/v1/notifications')
            ->assertJsonPath('data.unreadCount', 0);
    }

    public function test_user_can_reset_delivery_preferences_to_defaults(): void
    {
        $user        = EloquentUser::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/notifications/preferences', [
            'items' => [[
                'key'          => 'price_changes',
                'siteEnabled'  => false,
                'emailEnabled' => false,
            ]],
        ])->assertOk();

        $response    = $this->deleteJson('/api/v1/notifications/preferences')->assertOk();
        $priceChange = collect($response->json('data.items'))->firstWhere('key', 'price_changes');

        $this->assertTrue($priceChange['siteEnabled']);
        $this->assertTrue($priceChange['emailEnabled']);

        $this->assertDatabaseMissing('notification_preferences', [
            'user_id'   => $user->getKey(),
            'event_key' => 'price_changes',
        ]);
    }

    public function test_successful_sign_in_dispatches_security_notification(): void
    {
        Notification::fake();
        $user = EloquentUser::factory()->create([
            'email'    => 'notify-login@example.com',
            'password' => Hash::make('StrongPassword123!'),
        ]);

        $this->postJson('/api/v1/auth/sign-in', [
            'email'    => 'notify-login@example.com',
            'password' => 'StrongPassword123!',
        ])->assertOk();

        Notification::assertSentTo(
            $user,
            PlatformNotification::class,
            fn(PlatformNotification $notification): bool => $notification->eventType->value === 'security_login',
        );
    }
}
