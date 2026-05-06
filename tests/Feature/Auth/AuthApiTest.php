<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Mail\Infrastructure\Mails\GenericMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_sign_up_and_receive_token(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/auth/sign-up', [
            'name' => 'Imran',
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
            'email' => 'imran@example.com',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        Mail::assertSent(GenericMail::class, function (GenericMail $mail): bool {
            return $mail->hasTo('imran@example.com');
        });
    }

    public function test_sign_up_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/sign-up', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
                'passwordConfirmation',
            ]);
    }

    public function test_user_can_sign_in_and_receive_token(): void
    {
        $user = EloquentUser::factory()->create([
            'name' => 'Imran',
            'email' => 'imran@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/sign-in', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.userId', $user->id)
            ->assertJsonStructure([
                'data' => ['token', 'userId'],
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_sign_in_rejects_invalid_credentials(): void
    {
        $user = EloquentUser::factory()->create([
            'name' => 'Imran',
            'email' => 'imran@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/sign-in', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_signed_verify_email_marks_user_as_verified(): void
    {
        $user = EloquentUser::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verify-email',
            now()->addMinutes(60),
            ['user' => $user->id],
        );

        $response = $this->getJson($url);

        $response
            ->assertOk()
            ->assertJsonPath('data.verified', true);

        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
    }

    public function test_verify_email_rejects_invalid_signature(): void
    {
        $user = EloquentUser::factory()->unverified()->create();

        $response = $this->getJson("/api/v1/auth/verify-email?user={$user->id}");

        $response->assertForbidden();
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = EloquentUser::factory()->create([
            'name' => 'Imran',
            'email' => 'imran@example.com',
        ]);
        $token = $user->createToken('profile-test')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson('/api/v1/auth/me');

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Imran')
            ->assertJsonPath('data.email', 'imran@example.com');
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_logout_and_revoke_current_token(): void
    {
        $user = EloquentUser::factory()->create();
        $plainTextToken = $user->createToken('logout-test')->plainTextToken;
        $tokenId = $user->tokens()->latest('id')->value('id');

        $response = $this
            ->withToken($plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response
            ->assertOk()
            ->assertJsonPath('data.loggedOut', true);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/v1/auth/logout')
            ->assertUnauthorized();
    }
}
