<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Application\Jobs\SendEmailVerificationJob;
use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\FeatureTestCase;

class ResendEmailVerificationTest extends FeatureTestCase
{
    public function test_authenticated_user_can_resend_email_verification_code(): void
    {
        Queue::fake();

        $user = EloquentUser::factory()->unverified()->create([
            'email' => 'resend@example.com',
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/auth/email-verification-notification')
            ->assertOk()
            ->assertJsonPath('data.sent', true)
            ->assertJsonPath('data.message', 'Код подтверждения отправлен повторно.')
            ->assertJsonPath('data.cooldownSeconds', 60);

        Queue::assertPushed(
            SendEmailVerificationJob::class,
            fn(SendEmailVerificationJob $job): bool => $job->email === 'resend@example.com'
                && strlen($job->verificationCode) === 6
                && $job->expiresInMinutes === 60,
        );

        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.email-verification.requested',
            'user_id'  => $user->id,
        ]);
    }

    public function test_resend_email_verification_has_application_cooldown(): void
    {
        Queue::fake();

        $user = EloquentUser::factory()->unverified()->create([
            'email' => 'cooldown@example.com',
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/auth/email-verification-notification')
            ->assertOk()
            ->assertJsonPath('data.sent', true);

        $this->actingAs($user)
            ->postJson('/api/v1/auth/email-verification-notification')
            ->assertOk()
            ->assertJsonPath('data.sent', false)
            ->assertJsonPath('data.message', 'Новый код пока запрашивать рано. Попробуйте чуть позже.');

        $this->travel(61)->seconds();

        $this->actingAs($user)
            ->postJson('/api/v1/auth/email-verification-notification')
            ->assertOk()
            ->assertJsonPath('data.sent', true);

        $jobs = Queue::pushed(SendEmailVerificationJob::class);

        $this->assertCount(2, $jobs);
        $this->assertSame($jobs[0]->verificationCode, $jobs[1]->verificationCode);
    }

    public function test_verified_user_does_not_receive_new_verification_email(): void
    {
        Queue::fake();

        $user = EloquentUser::factory()->create([
            'email' => 'verified@example.com',
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/auth/email-verification-notification')
            ->assertOk()
            ->assertJsonPath('data.sent', false)
            ->assertJsonPath('data.message', 'Email уже подтвержден.');

        Queue::assertNothingPushed();
    }
}
