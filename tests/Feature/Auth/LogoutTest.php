<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use Tests\Feature\FeatureTestCase;

class LogoutTest extends FeatureTestCase
{
    public function test_authenticated_user_can_logout_from_web_session(): void
    {
        $user           = EloquentUser::factory()->create([
            'first_name' => 'Imran',
            'last_name'  => 'Khan',
            'email'      => 'imran@example.com',
            'password'   => 'StrongPassword123!',
        ]);

        $response       = $this
            ->actingAs($user)
            ->postJson('/api/v1/auth/logout');

        $response
            ->assertOk()
            ->assertJsonPath('data.loggedOut', true)
            ->assertJsonPath('data.message', 'Вы успешно вышли из аккаунта.');

        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.logout',
            'user_id'  => $user->id,
        ]);
    }
}
