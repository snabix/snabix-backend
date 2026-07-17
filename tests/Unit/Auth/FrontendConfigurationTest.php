<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use Tests\TestCase;

class FrontendConfigurationTest extends TestCase
{
    public function test_example_reset_password_url_targets_existing_frontend_route(): void
    {
        $example = file_get_contents(base_path('.env.example'));

        $this->assertIsString($example);
        $this->assertStringContainsString(
            'FRONTEND_RESET_PASSWORD_URL=${FRONTEND_URL}/reset-password',
            $example,
        );
        $this->assertStringNotContainsString(
            'FRONTEND_RESET_PASSWORD_URL=${FRONTEND_URL}/auth/reset-password',
            $example,
        );
    }
}
