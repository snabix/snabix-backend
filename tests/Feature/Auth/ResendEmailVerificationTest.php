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
            ->assertJsonPath('data.message', 'Код подтверждения отправлен повторно.');

        Queue::assertPushed(
            SendEmailVerificationJob::class,
            fn(SendEmailVerificationJob $job): bool => $job->email === 'resend@example.com'
                && strlen($job->verificationCode) === 6
                && $job->expiresInMinutes === 60,
        );
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
