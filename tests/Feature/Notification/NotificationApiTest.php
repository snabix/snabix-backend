<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Notification\Application\Notifications\PlatformNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\FeatureTestCase;

class NotificationApiTest extends FeatureTestCase
{
    /**
     * @return list<array{key: string, siteEnabled: bool, emailEnabled: bool}>
     */
    private static function preferenceItems(mixed $items): array
    {
        if (! is_array($items)) {
            throw new InvalidArgumentException('Notification preferences response items must be an array.');
        }

        return array_values(array_map(
            static function (mixed $item): array {
                if (
                    ! is_array($item)
                    || ! is_string($item['key'] ?? null)
                    || ! is_bool($item['siteEnabled'] ?? null)
                    || ! is_bool($item['emailEnabled'] ?? null)
                ) {
                    throw new InvalidArgumentException('Notification preference item has invalid shape.');
                }

                return [
                    'key'          => $item['key'],
                    'siteEnabled'  => $item['siteEnabled'],
                    'emailEnabled' => $item['emailEnabled'],
                ];
            },
            $items,
        ));
    }

    public function test_user_can_read_and_update_delivery_preferences(): void
    {
        $user            = EloquentUser::factory()->create();
        Sanctum::actingAs($user);

        $response        = $this->getJson('/api/v1/notifications/preferences')
            ->assertOk()
            ->assertJsonCount(11, 'data.items');

        $items           = self::preferenceItems($response->json('data.items'));
        $security        = collect($items)->firstWhere('key', 'security_login');

        $this->assertNotNull($security);
        $this->assertTrue($security['siteEnabled']);

        $updatedResponse = $this->putJson('/api/v1/notifications/preferences', [
            'items' => [[
                'key'          => 'security_login',
                'siteEnabled'  => false,
                'emailEnabled' => false,
            ]],
        ])->assertOk();
        $updatedSecurity = collect(self::preferenceItems($updatedResponse->json('data.items')))->firstWhere('key', 'security_login');

        $this->assertNotNull($updatedSecurity);
        $this->assertTrue($updatedSecurity['siteEnabled']);
        $this->assertFalse($updatedSecurity['emailEnabled']);
    }

    public function test_listing_moderation_notification_is_required_for_site_and_email(): void
    {
        $user       = EloquentUser::factory()->create();
        Sanctum::actingAs($user);

        $response   = $this->putJson('/api/v1/notifications/preferences', [
            'items' => [[
                'key'          => 'listing_moderation',
                'siteEnabled'  => false,
                'emailEnabled' => false,
            ]],
        ])->assertOk();

        $moderation = collect(self::preferenceItems($response->json('data.items')))->firstWhere('key', 'listing_moderation');

        $this->assertNotNull($moderation);
        $this->assertTrue($moderation['siteEnabled']);
        $this->assertTrue($moderation['emailEnabled']);
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
        $this->assertIsString($id);

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
        $priceChange = collect(self::preferenceItems($response->json('data.items')))->firstWhere('key', 'price_changes');

        $this->assertNotNull($priceChange);
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
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-09 12:53:00', 'UTC'));

        $user = EloquentUser::factory()->create([
            'email'      => 'notify-login@example.com',
            'first_name' => 'Test',
            'last_name'  => 'User',
            'password'   => Hash::make('StrongPassword123!'),
        ]);

        $this
            ->withServerVariables([
                'REMOTE_ADDR'      => '89.105.210.66',
                'HTTP_USER_AGENT'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0',
            ])
            ->postJson('/api/v1/auth/sign-in', [
                'email'    => 'notify-login@example.com',
                'password' => 'StrongPassword123!',
            ])
            ->assertOk();

        Notification::assertSentTo(
            $user,
            PlatformNotification::class,
            function (PlatformNotification $notification) use ($user): bool {
                $loginDetails = $notification->context['loginDetails'] ?? null;
                $mailMessage  = $notification->toMail($user);
                $viewDetails  = $mailMessage->viewData['details'] ?? null;
                $renderedMail = $mailMessage->view === 'emails.security-login'
                    ? view('emails.security-login', $mailMessage->viewData)->render()
                    : '';

                return $notification->eventType->value === 'security_login'
                    && is_array($loginDetails)
                    && $loginDetails['location'] === 'По IP: 89.105.210.66'
                    && $loginDetails['device'] === 'macOS устройство'
                    && $loginDetails['browser'] === 'Firefox'
                    && $loginDetails['ipAddress'] === '89.105.210.66'
                    && $loginDetails['signedInAt'] === '09.07.2026 12:53:00 UTC'
                    && $mailMessage->view === 'emails.security-login'
                    && $mailMessage->viewData['accountLabel'] === 'Test User'
                    && $mailMessage->viewData['sessionsUrl'] === 'http://localhost:3000/account/settings/sessions'
                    && is_array($viewDetails)
                    && $viewDetails['location'] === 'По IP: 89.105.210.66'
                    && $viewDetails['device'] === 'macOS устройство'
                    && $viewDetails['browser'] === 'Firefox'
                    && str_contains($renderedMail, 'Ваша учетная запись SNABIX')
                    && str_contains($renderedMail, 'Проверить активные сессии')
                    && str_contains($renderedMail, '89.105.210.66');
            },
        );

        CarbonImmutable::setTestNow();
    }
}
