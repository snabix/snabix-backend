<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use Tests\Feature\FeatureTestCase;

class SignInTest extends FeatureTestCase
{
    public function test_user_can_sign_in_with_valid_credentials(): void
    {
        $user = EloquentUser::factory()->create([
            'name' => 'Imran',
            'email' => 'imran@example.com',
            'password' => 'StrongPassword123!',
        ]);

        $response = $this->postJson('/api/v1/auth/sign-in', [
            'email' => 'imran@example.com',
            'password' => 'StrongPassword123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.userId', $user->id);

        $token = $response->json('data.token');

        $this->assertIsString($token);
        $this->assertNotSame('', $token);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action' => 'auth.sign-in',
            'user_id' => $user->id,
        ]);
    }

    public function test_failed_sign_in_is_logged(): void
    {
        EloquentUser::factory()->create([
            'name' => 'Imran',
            'email' => 'imran@example.com',
            'password' => 'StrongPassword123!',
        ]);

        $this->postJson('/api/v1/auth/sign-in', [
            'email' => 'imran@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action' => 'auth.sign-in.failed',
        ]);
    }
}
