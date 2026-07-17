<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTestCase;

class ResetPasswordTest extends FeatureTestCase
{
    public function test_user_can_reset_password_with_valid_token(): void
    {
        $email     = sprintf('reset-password-%s@example.com', Str::uuid()->toString());

        RateLimiter::clear($email . '|127.0.0.1');

        $user      = EloquentUser::factory()->create([
            'email'    => $email,
            'password' => 'OldStrongPassword123!',
        ]);
        $otherUser = EloquentUser::factory()->create();

        DB::table('sessions')->insert([
            [
                'id'            => 'reset-session-one',
                'user_id'       => $user->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id'            => 'reset-session-two',
                'user_id'       => $user->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id'            => 'other-user-session',
                'user_id'       => $otherUser->id,
                'ip_address'    => null,
                'user_agent'    => null,
                'payload'       => 'serialized-payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        $token     = Password::broker('users')->createToken($user);

        $response  = $this->postJson('/api/v1/auth/reset-password', [
            'email'                => $user->email,
            'token'                => $token,
            'password'             => 'NewStrongPassword123!',
            'passwordConfirmation' => 'NewStrongPassword123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.reset', true);

        $freshUser = $user->fresh();

        $this->assertInstanceOf(EloquentUser::class, $freshUser);
        $this->assertTrue(Hash::check('NewStrongPassword123!', $freshUser->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'reset-session-one']);
        $this->assertDatabaseMissing('sessions', ['id' => 'reset-session-two']);
        $this->assertDatabaseHas('sessions', ['id' => 'other-user-session']);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.reset-password',
            'user_id'  => $user->id,
        ]);
    }
}
