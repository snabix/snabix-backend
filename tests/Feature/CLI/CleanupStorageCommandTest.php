<?php

declare(strict_types=1);

namespace Tests\Feature\CLI;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CleanupStorageCommandTest extends TestCase
{
    private string $cleanupPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanupPath = storage_path('framework/testing/storage-cleanup');

        File::deleteDirectory($this->cleanupPath);
        File::ensureDirectoryExists($this->cleanupPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->cleanupPath);

        parent::tearDown();
    }

    public function test_it_removes_only_expired_files_for_configured_scope(): void
    {
        $oldFile   = $this->putFile('logs/old.log', 'old');
        $freshFile = $this->putFile('logs/fresh.log', 'fresh');
        $ignored   = $this->putFile('logs/.gitignore', '*');

        touch($oldFile, now()->subDays(10)->getTimestamp());
        touch($freshFile, now()->subDay()->getTimestamp());
        touch($ignored, now()->subDays(10)->getTimestamp());

        config()->set('storage-cleanup.entries', [
            'test_logs' => [
                'enabled'                  => true,
                'path'                     => $this->cleanupPath . '/logs',
                'retention_days'           => 7,
                'patterns'                 => ['*.log'],
                'delete_empty_directories' => true,
            ],
        ]);

        $exitCode  = Artisan::call('shared:cleanup-storage', [
            '--scope' => ['test_logs'],
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertFileDoesNotExist($oldFile);
        $this->assertFileExists($freshFile);
        $this->assertFileExists($ignored);
    }

    public function test_dry_run_keeps_expired_files(): void
    {
        $oldFile  = $this->putFile('api-docs/openapi.json', '{}');

        touch($oldFile, now()->subDays(40)->getTimestamp());

        config()->set('storage-cleanup.entries', [
            'test_api_docs' => [
                'enabled'                  => true,
                'path'                     => $this->cleanupPath . '/api-docs',
                'retention_days'           => 30,
                'patterns'                 => ['*'],
                'delete_empty_directories' => true,
            ],
        ]);

        $exitCode = Artisan::call('shared:cleanup-storage', [
            '--scope'  => ['test_api_docs'],
            '--dry-run'=> true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($oldFile);
    }

    private function putFile(string $relativePath, string $contents): string
    {
        $path = $this->cleanupPath . '/' . $relativePath;

        File::ensureDirectoryExists((string) dirname($path));
        File::put($path, $contents);

        return $path;
    }
}
