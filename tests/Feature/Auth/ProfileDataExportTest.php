<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Mail\Infrastructure\Mails\GenericMail;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\FeatureTestCase;

class ProfileDataExportTest extends FeatureTestCase
{
    public function test_authenticated_user_can_request_profile_data_export_email(): void
    {
        Mail::fake();

        $user = EloquentUser::factory()->create([
            'first_name'   => 'Imran',
            'last_name'    => 'Khan',
            'email'        => 'privacy@example.com',
            'phone_number' => '+79991234567',
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/auth/me/data-export')
            ->assertOk()
            ->assertJsonPath('data.requested', true)
            ->assertJsonPath('data.message', 'Запрос отправлен. Письмо с данными профиля придет на email аккаунта.');

        Mail::assertSent(
            GenericMail::class,
            fn(GenericMail $mail): bool => $mail->hasTo('privacy@example.com'),
        );
    }
}
