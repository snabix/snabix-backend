<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\Feature\FeatureTestCase;

class ResetPasswordTest extends FeatureTestCase
{
    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = EloquentUser::factory()->create([
            'email' => 'imran@example.com',
            'password' => 'OldStrongPassword123!',
        ]);

        $token = Password::broker('users')->createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewStrongPassword123!',
            'passwordConfirmation' => 'NewStrongPassword123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.reset', true);

        $freshUser = $user->fresh();

        $this->assertInstanceOf(EloquentUser::class, $freshUser);
        $this->assertTrue(Hash::check('NewStrongPassword123!', $freshUser->password));
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action' => 'auth.reset-password',
            'user_id' => $user->id,
        ]);
    }
}
