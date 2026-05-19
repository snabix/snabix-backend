<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\Feature\FeatureTestCase;

class SessionExpirationPolicyTest extends FeatureTestCase
{
    public function test_unauthenticated_api_request_returns_session_expired_contract(): void
    {
        $this
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Сессия истекла или пользователь не авторизован.')
            ->assertJsonPath('code', 'auth.unauthenticated')
            ->assertJsonPath('sessionExpired', true);
    }
}
