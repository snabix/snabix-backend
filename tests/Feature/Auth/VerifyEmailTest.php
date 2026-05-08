<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Support\Facades\URL;
use Tests\Feature\FeatureTestCase;

class VerifyEmailTest extends FeatureTestCase
{
    public function test_signed_verification_link_marks_email_as_verified_and_redirects_to_frontend(): void
    {
        config()->set('frontend.email_verification_redirect_url', 'https://frontend.test/auth/email-verified');

        $user = EloquentUser::factory()->unverified()->create([
            'first_name' => 'Unverified',
            'last_name' => 'User',
            'email' => 'unverified@example.com',
            'password' => 'StrongPassword123!',
        ]);

        $url = URL::temporarySignedRoute(
            'verify-email',
            now()->addMinutes(60),
            ['user' => $user->id],
        );

        $response = $this->get($url);

        $response
            ->assertRedirect('https://frontend.test/auth/email-verified?verified=1&user='.$user->id);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);

        $freshUser = $user->fresh();

        $this->assertInstanceOf(EloquentUser::class, $freshUser);
        $this->assertTrue($freshUser->email_verified_at !== null);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action' => 'auth.verify-email',
            'user_id' => $user->id,
        ]);
    }
}
