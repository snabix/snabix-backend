<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Application\Jobs\SendPasswordResetJob;
use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTestCase;

class ForgotPasswordTest extends FeatureTestCase
{
    public function test_user_can_request_password_reset_instructions(): void
    {
        Queue::fake();
        config()->set('frontend.reset_password_url', 'https://app.snabix.test/reset-password');

        $email    = sprintf('forgot-password-%s@example.com', Str::uuid()->toString());

        RateLimiter::clear($email . '|127.0.0.1');

        $user     = EloquentUser::factory()->create([
            'email' => $email,
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.sent', true);

        Queue::assertPushed(
            SendPasswordResetJob::class,
            fn(SendPasswordResetJob $job): bool => $job->email === $user->email
                && str_starts_with($job->resetUrl, 'https://app.snabix.test/reset-password?')
                && str_contains($job->resetUrl, 'token=')
                && str_contains($job->resetUrl, 'email=' . urlencode($user->email)),
        );

        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.forgot-password',
            'user_id'  => $user->id,
        ]);
    }
}
