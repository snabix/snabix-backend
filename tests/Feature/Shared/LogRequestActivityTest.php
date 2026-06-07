<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use App\Auth\Infrastructure\Models\EloquentUser;
use Tests\Feature\FeatureTestCase;

class LogRequestActivityTest extends FeatureTestCase
{
    public function test_api_requests_are_logged_via_middleware(): void
    {
        EloquentUser::factory()->create([
            'first_name' => 'Imran',
            'last_name'  => 'Khan',
            'email'      => 'imran@example.com',
            'password'   => 'StrongPassword123!',
        ]);

        $this->postJson('/api/v1/auth/sign-in', [
            'email'    => 'imran@example.com',
            'password' => 'StrongPassword123!',
        ])->assertOk();

        $this->assertDatabaseHas('system_logs', [
            'category'    => 'http',
            'method'      => 'POST',
            'path'        => '/api/v1/auth/sign-in',
            'status_code' => 200,
        ]);
    }
}
