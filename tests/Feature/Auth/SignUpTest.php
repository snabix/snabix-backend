<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Application\Jobs\SendEmailVerificationJob;
use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\FeatureTestCase;

class SignUpTest extends FeatureTestCase
{
    public function test_user_can_sign_up_and_send_email_verification_message(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/auth/sign-up', [
            'firstName' => 'Imran',
            'lastName' => 'Khan',
            'phoneNumber' => '+79991234567',
            'email' => 'imran@example.com',
            'password' => 'StrongPassword123!',
            'passwordConfirmation' => 'StrongPassword123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['token'],
            ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Imran',
            'last_name' => 'Khan',
            'phone_number' => '+79991234567',
            'email' => 'imran@example.com',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action' => 'auth.sign-up',
            'user_id' => EloquentUser::query()->where('email', 'imran@example.com')->value('id'),
        ]);

        Queue::assertPushed(
            SendEmailVerificationJob::class,
            fn(SendEmailVerificationJob $job): bool => $job->email === 'imran@example.com'
                && $job->queue === 'notifications',
        );
    }
}
