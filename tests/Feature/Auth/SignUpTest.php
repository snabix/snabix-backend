<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Application\Jobs\SendEmailVerificationJob;
use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTestCase;

class SignUpTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_user_can_sign_up_and_send_email_verification_message(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/auth/sign-up', [
            'firstName'            => 'Imran',
            'lastName'             => 'Khan',
            'email'                => 'imran@example.com',
            'password'             => 'StrongPassword123!',
            'passwordConfirmation' => 'StrongPassword123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['userId'],
            ]);

        $this->assertDatabaseHas('users', [
            'first_name'   => 'Imran',
            'last_name'    => 'Khan',
            'phone_number' => null,
            'email'        => 'imran@example.com',
            'is_active'    => true,
        ]);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.sign-up',
            'user_id'  => EloquentUser::query()->where('email', 'imran@example.com')->value('id'),
        ]);

        Queue::assertPushed(
            SendEmailVerificationJob::class,
            fn(SendEmailVerificationJob $job): bool => $job->email === 'imran@example.com'
                && $job->queue === 'notifications',
        );
    }

    public function test_user_can_sign_up_with_only_email_and_password(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/auth/sign-up', [
            'email'                => 'minimal@example.com',
            'password'             => 'StrongPassword123!',
            'passwordConfirmation' => 'StrongPassword123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['userId'],
            ]);

        $this->assertDatabaseHas('users', [
            'first_name'   => null,
            'last_name'    => null,
            'phone_number' => null,
            'email'        => 'minimal@example.com',
            'is_active'    => true,
        ]);

        Queue::assertPushed(
            SendEmailVerificationJob::class,
            fn(SendEmailVerificationJob $job): bool => $job->email === 'minimal@example.com'
                && $job->name === 'minimal@example.com',
        );
    }

    public function test_sign_up_replays_same_result_for_same_idempotency_key(): void
    {
        Queue::fake();

        $payload = [
            'email'                => 'idempotent@example.com',
            'password'             => 'StrongPassword123!',
            'passwordConfirmation' => 'StrongPassword123!',
        ];
        $key     = 'signup-019f4f54-19c2-7f39-a778-e328b85cd690';

        $first   = $this
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/auth/sign-up', $payload)
            ->assertOk();

        $second  = $this
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/auth/sign-up', $payload)
            ->assertOk();

        $this->assertSame($first->json('data.userId'), $second->json('data.userId'));
        $this->assertSame(1, EloquentUser::query()->where('email', $payload['email'])->count());
        $this->assertDatabaseCount('idempotency_keys', 1);
    }

    public function test_sign_up_rejects_reused_idempotency_key_for_changed_payload(): void
    {
        Queue::fake();

        $key     = 'signup-019f4f54-19c2-7f39-a778-e328b85cd691';
        $payload = [
            'email'                => 'idempotency-conflict@example.com',
            'password'             => 'StrongPassword123!',
            'passwordConfirmation' => 'StrongPassword123!',
        ];

        $this
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/auth/sign-up', $payload)
            ->assertOk();

        $this
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/auth/sign-up', [
                ...$payload,
                'firstName' => 'Другой',
            ])
            ->assertConflict()
            ->assertJsonPath('code', 'request.idempotency-conflict');

        $this->assertSame(1, EloquentUser::query()->where('email', $payload['email'])->count());
    }

    public function test_sign_up_rejects_invalid_idempotency_key(): void
    {
        $this
            ->withHeader('Idempotency-Key', 'bad key')
            ->postJson('/api/v1/auth/sign-up', [
                'email'                => 'invalid-key@example.com',
                'password'             => 'StrongPassword123!',
                'passwordConfirmation' => 'StrongPassword123!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['idempotencyKey']);
    }

    public function test_concurrent_duplicate_email_unique_violation_returns_validation_error(): void
    {
        Queue::fake();

        $email    = 'signup-race@example.com';
        $injected = false;

        DB::listen(function (QueryExecuted $query) use ($email, &$injected): void {
            if (
                $injected
                || ! str_contains($query->sql, 'select exists')
                || ! str_contains($query->sql, '"users"')
                || ! in_array($email, $query->bindings, true)
            ) {
                return;
            }

            $injected = true;

            DB::table('users')->insert([
                'id'         => (string) Str::uuid(),
                'first_name' => 'Concurrent',
                'last_name'  => 'Request',
                'email'      => $email,
                'password'   => Hash::make('StrongPassword123!'),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $this
            ->postJson('/api/v1/auth/sign-up', [
                'email'                => $email,
                'password'             => 'StrongPassword123!',
                'passwordConfirmation' => 'StrongPassword123!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertTrue($injected);
        $this->assertDatabaseMissing('users', ['email' => $email]);
    }
}
