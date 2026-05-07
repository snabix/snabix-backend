<?php

declare(strict_types=1);

namespace Tests\Feature\Docs;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SwaggerGenerationTest extends TestCase
{
    public function test_swagger_docs_can_be_generated_and_contain_auth_paths(): void
    {
        $docsPath = storage_path('api-docs/api-docs.json');

        File::delete($docsPath);

        $exitCode = Artisan::call('l5-swagger:generate');

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($docsPath);

        $contents = File::get($docsPath);

        $this->assertJson($contents);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        $paths = $decoded['paths'] ?? null;

        $this->assertArrayHasKey('paths', $decoded);
        $this->assertIsArray($paths);
        $this->assertArrayHasKey('/api/v1/auth/sign-up', $paths);
        $this->assertArrayHasKey('/api/v1/auth/sign-in', $paths);
        $this->assertArrayHasKey('/api/v1/auth/forgot-password', $paths);
        $this->assertArrayHasKey('/api/v1/auth/reset-password', $paths);
        $this->assertArrayHasKey('/api/v1/auth/verify-email', $paths);
        $this->assertArrayHasKey('/api/v1/auth/me', $paths);
        $this->assertArrayHasKey('/api/v1/auth/logout', $paths);
    }

    public function test_swagger_http_endpoints_are_accessible_after_generation(): void
    {
        Artisan::call('l5-swagger:generate');

        $this->get('/docs')
            ->assertOk()
            ->assertHeader('content-type', 'application/json');

        $this->get('/api/documentation')
            ->assertOk()
            ->assertSee('swagger-ui', escape: false);
    }
}
