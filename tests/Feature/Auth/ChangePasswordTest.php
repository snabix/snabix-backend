<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\FeatureTestCase;

class ChangePasswordTest extends FeatureTestCase
{
    public function test_authenticated_user_can_change_password(): void
    {
        $user             = EloquentUser::factory()->create([
            'email'    => 'change-password@example.com',
            'password' => 'OldStrongPassword123!',
        ]);
        $otherUser        = EloquentUser::factory()->create();

        $this->withSession(['auth-flow' => 'change-password']);

        $currentSessionId = $this->app['session']->getId();

        DB::table('sessions')->insert([
            [
                'id'            => $currentSessionId,
                'user_id'       => $user->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id'            => 'other-user-device-session',
                'user_id'       => $user->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id'            => 'foreign-user-session',
                'user_id'       => $otherUser->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/auth/change-password', [
                'currentPassword'      => 'OldStrongPassword123!',
                'password'             => 'NewStrongPassword123!',
                'passwordConfirmation' => 'NewStrongPassword123!',
            ])
            ->assertOk()
            ->assertJsonPath('data.changed', true)
            ->assertJsonPath('data.message', 'Пароль успешно изменен.');

        $freshUser        = $user->fresh();

        $this->assertInstanceOf(EloquentUser::class, $freshUser);
        $this->assertTrue(Hash::check('NewStrongPassword123!', $freshUser->password));
        $this->assertDatabaseMissing('sessions', ['id' => $currentSessionId]);
        $this->assertDatabaseMissing('sessions', ['id' => 'other-user-device-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'foreign-user-session']);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.change-password',
            'user_id'  => $user->id,
        ]);
    }

    public function test_current_password_must_be_valid_to_change_password(): void
    {
        $user      = EloquentUser::factory()->create([
            'password' => 'OldStrongPassword123!',
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/auth/change-password', [
                'currentPassword'      => 'WrongStrongPassword123!',
                'password'             => 'NewStrongPassword123!',
                'passwordConfirmation' => 'NewStrongPassword123!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['currentPassword']);

        $freshUser = $user->fresh();

        $this->assertInstanceOf(EloquentUser::class, $freshUser);
        $this->assertTrue(Hash::check('OldStrongPassword123!', $freshUser->password));
    }
}
