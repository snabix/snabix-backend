<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use Tests\Feature\FeatureTestCase;

class ProfileTest extends FeatureTestCase
{
    public function test_profile_returns_null_instead_of_fabricated_names(): void
    {
        $user = EloquentUser::factory()
            ->withoutName()
            ->create(['email' => 'unnamed@example.com']);

        $this->actingAs($user)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.firstName', null)
            ->assertJsonPath('data.lastName', null)
            ->assertJsonPath('data.email', 'unnamed@example.com');
    }

    public function test_authenticated_user_can_get_profile_with_new_user_fields(): void
    {
        $user  = EloquentUser::factory()->create([
            'first_name'   => 'Imran',
            'last_name'    => 'Khan',
            'description'  => 'Поставляю инженерное оборудование и быстро отвечаю на заявки.',
            'date_of_birth'=> '1994-05-12',
            'phone_number' => '+79991234567',
            'email'        => 'imran@example.com',
            'is_active'    => true,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.firstName', 'Imran')
            ->assertJsonPath('data.lastName', 'Khan')
            ->assertJsonPath('data.description', 'Поставляю инженерное оборудование и быстро отвечаю на заявки.')
            ->assertJsonPath('data.dateOfBirth', '1994-05-12')
            ->assertJsonPath('data.email', 'imran@example.com')
            ->assertJsonPath('data.phoneNumber', '+79991234567')
            ->assertJsonPath('data.isActive', true);
    }
}
