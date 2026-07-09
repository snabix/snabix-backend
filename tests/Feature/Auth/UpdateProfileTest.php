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

        $user      = EloquentUser::factory()->create([
            'first_name'   => 'Old',
            'last_name'    => 'Name',
            'about'        => null,
            'phone_number' => '+79991112233',
            'email'        => 'old@example.com',
        ]);

        $response  = $this
            ->actingAs($user)
            ->patchJson('/api/v1/auth/me', [
                'firstName'   => 'New',
                'lastName'    => 'Person',
                'aboutMe'     => 'Работаю с промышленными поставками по югу России.',
                'email'       => 'new@example.com',
                'phoneNumber' => '+79994445566',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.firstName', 'New')
            ->assertJsonPath('data.lastName', 'Person')
            ->assertJsonPath('data.aboutMe', 'Работаю с промышленными поставками по югу России.')
            ->assertJsonPath('data.email', 'new@example.com')
            ->assertJsonPath('data.phoneNumber', '+79994445566')
            ->assertJsonPath('data.isActive', true)
            ->assertJsonPath('data.emailVerifiedAt', null);

        $freshUser = $user->fresh();

        $this->assertInstanceOf(EloquentUser::class, $freshUser);
        $this->assertSame('New', $freshUser->first_name);
        $this->assertSame('Person', $freshUser->last_name);
        $this->assertSame('Работаю с промышленными поставками по югу России.', $freshUser->about);
        $this->assertSame('new@example.com', $freshUser->email);
        $this->assertSame('+79994445566', $freshUser->phone_number);
        $this->assertNull($freshUser->email_verified_at);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.profile.update',
            'user_id'  => $user->id,
        ]);

        Queue::assertPushed(
            SendEmailVerificationJob::class,
            fn(SendEmailVerificationJob $job): bool => $job->email === 'new@example.com',
        );
    }
}
