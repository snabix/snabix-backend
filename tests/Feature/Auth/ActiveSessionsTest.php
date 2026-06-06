<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Support\Facades\DB;
use Tests\Feature\FeatureTestCase;

class ActiveSessionsTest extends FeatureTestCase
{
    public function test_authenticated_user_can_list_active_sessions(): void
    {
        $user = EloquentUser::factory()->create();

        DB::table('sessions')->insert([
            [
                'id'            => 'desktop-session',
                'user_id'       => $user->id,
                'ip_address'    => '127.0.0.1',
                'user_agent'    => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/124.0 Safari/537.36',
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id'            => 'mobile-session',
                'user_id'       => $user->id,
                'ip_address'    => '127.0.0.2',
                'user_agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 Version/17.5 Mobile/15E148 Safari/604.1',
                'payload'       => 'serialized-payload',
                'last_activity' => now()->subMinute()->timestamp,
            ],
        ]);

        $this
            ->actingAs($user)
            ->getJson('/api/v1/auth/sessions')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', 'desktop-session')
            ->assertJsonPath('data.items.0.deviceName', 'macOS устройство')
            ->assertJsonPath('data.items.0.browser', 'Chrome')
            ->assertJsonPath('data.items.1.type', 'mobile');
    }

    public function test_authenticated_user_can_terminate_one_session(): void
    {
        $user      = EloquentUser::factory()->create();
        $otherUser = EloquentUser::factory()->create();

        DB::table('sessions')->insert([
            [
                'id'            => 'owned-session',
                'user_id'       => $user->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id'            => 'other-session',
                'user_id'       => $otherUser->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        $this
            ->actingAs($user)
            ->deleteJson('/api/v1/auth/sessions/owned-session')
            ->assertOk()
            ->assertJsonPath('data.terminated', true);

        $this->assertDatabaseMissing('sessions', ['id' => 'owned-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'other-session']);
    }

    public function test_authenticated_user_can_terminate_other_sessions(): void
    {
        $user = EloquentUser::factory()->create();

        DB::table('sessions')->insert([
            [
                'id'            => 'first-session',
                'user_id'       => $user->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id'            => 'second-session',
                'user_id'       => $user->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        $this
            ->actingAs($user)
            ->deleteJson('/api/v1/auth/sessions')
            ->assertOk()
            ->assertJsonPath('data.terminated', true)
            ->assertJsonPath('data.terminatedCount', 2);

        $this->assertDatabaseMissing('sessions', ['id' => 'first-session']);
        $this->assertDatabaseMissing('sessions', ['id' => 'second-session']);
    }
}
