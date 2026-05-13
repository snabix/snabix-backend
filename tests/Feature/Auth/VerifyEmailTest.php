<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Application\Services\EmailVerificationCodeService;
use App\Auth\Infrastructure\Models\EloquentUser;
use Tests\Feature\FeatureTestCase;

class VerifyEmailTest extends FeatureTestCase
{
    public function test_authenticated_user_can_verify_email_by_code(): void
    {
        $user = EloquentUser::factory()->unverified()->create([
            'first_name' => 'Unverified',
            'last_name'  => 'User',
            'email'      => 'unverified@example.com',
            'password'   => 'StrongPassword123!',
        ]);

        /** @var EmailVerificationCodeService $emailVerificationCodeService */
        $emailVerificationCodeService = app(EmailVerificationCodeService::class);
        $code = $emailVerificationCodeService->issue((string) $user->id, $user->email);

        $this->actingAs($user)
            ->postJson('/api/v1/auth/verify-email', [
                'code' => $code,
            ])
            ->assertOk()
            ->assertJsonPath('data.verified', true)
            ->assertJsonPath('data.alreadyVerified', false)
            ->assertJsonPath('data.message', 'Email успешно подтвержден.');

        $freshUser = $user->fresh();

        $this->assertInstanceOf(EloquentUser::class, $freshUser);
        $this->assertTrue($freshUser->email_verified_at !== null);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.verify-email',
            'user_id'  => $user->id,
        ]);
    }

    public function test_verified_user_receives_already_verified_response(): void
    {
        $user = EloquentUser::factory()->create([
            'email' => 'repeat@example.com',
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/auth/verify-email', [
                'code' => '123456',
            ])
            ->assertOk()
            ->assertJsonPath('data.verified', false)
            ->assertJsonPath('data.alreadyVerified', true)
            ->assertJsonPath('data.message', 'Email уже подтвержден.');
    }
}
