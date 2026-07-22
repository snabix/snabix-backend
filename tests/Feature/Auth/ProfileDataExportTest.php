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

        $user = EloquentUser::factory()->withoutName()->create([
            'description'        => 'Описание профиля для экспорта.',
            'date_of_birth'      => '1994-05-12',
            'email'              => 'privacy@example.com',
            'phone_number'       => '+79991234567',
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/auth/me/data-export')
            ->assertOk()
            ->assertJsonPath('data.requested', true)
            ->assertJsonPath('data.message', 'Запрос отправлен. Письмо с данными профиля придет на email аккаунта.');

        Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($user): bool {
            if (! $mail->hasTo('privacy@example.com')) {
                return false;
            }

            if (($mail->content()->with['accountLabel'] ?? null) !== 'privacy@example.com') {
                return false;
            }

            $attachment = $mail->attachments()[0] ?? null;

            if ($attachment === null || $attachment->as !== 'snabix-profile-data.json' || $attachment->mime !== 'application/json') {
                return false;
            }

            $contents   = $attachment->attachWith(
                static fn(): array => [],
                static fn(callable $data): string => $data(),
            );

            if (! is_string($contents)) {
                return false;
            }

            return str_contains($contents, '"id": "' . $user->id . '"')
                && str_contains($contents, '"firstName": null')
                && str_contains($contents, '"lastName": null')
                && str_contains($contents, '"email": "privacy@example.com"')
                && str_contains($contents, '"description": "Описание профиля для экспорта."')
                && str_contains($contents, '"dateOfBirth": "1994-05-12"')
                && str_contains($contents, '"phoneNumber": "+79991234567"')
                && str_contains($contents, '"password": "not_exported"');
        });
    }
}
