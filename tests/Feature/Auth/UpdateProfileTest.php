<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Application\Jobs\SendEmailVerificationJob;
use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\FeatureTestCase;

class UpdateProfileTest extends FeatureTestCase
{
    public function test_authenticated_user_can_update_profile_and_reverify_email_when_it_changes(): void
    {
        Queue::fake();

        $user = EloquentUser::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->patchJson('/api/v1/auth/me', [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.email', 'new@example.com')
            ->assertJsonPath('data.emailVerifiedAt', null);

        $freshUser = $user->fresh();

        $this->assertInstanceOf(EloquentUser::class, $freshUser);
        $this->assertSame('New Name', $freshUser->name);
        $this->assertSame('new@example.com', $freshUser->email);
        $this->assertNull($freshUser->email_verified_at);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action' => 'auth.profile.update',
            'user_id' => $user->id,
        ]);

        Queue::assertPushed(
            SendEmailVerificationJob::class,
            fn(SendEmailVerificationJob $job): bool => $job->email === 'new@example.com',
        );
    }
}
