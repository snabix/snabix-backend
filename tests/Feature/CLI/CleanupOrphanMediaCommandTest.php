<?php

declare(strict_types=1);

namespace Tests\Feature\CLI;

use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTestCase;

class CleanupOrphanMediaCommandTest extends FeatureTestCase
{
    private string $diskRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->diskRoot = storage_path('framework/testing/orphan-media/public');

        File::deleteDirectory($this->diskRoot);
        File::ensureDirectoryExists($this->diskRoot);

        config()->set('filesystems.disks.public.root', $this->diskRoot);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->diskRoot);

        parent::tearDown();
    }

    public function test_dry_run_finds_orphan_media_without_deleting_files(): void
    {
        $orphanFile = $this->putOldFile('images/listing-images/orphan/photo.jpg', 'orphan');

        $exitCode   = Artisan::call('media:cleanup-orphans', [
            '--disk' => ['public'],
            '--days' => 7,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($orphanFile);
    }

    public function test_force_deletes_only_old_files_without_media_record(): void
    {
        $mediaUuid      = (string) Str::uuid();
        $referencedFile = $this->putOldFile("images/listing-images/{$mediaUuid}/photo.jpg", 'referenced');
        $orphanFile     = $this->putOldFile('images/listing-images/orphan/photo.jpg', 'orphan');
        $freshOrphan    = $this->putFile('images/listing-images/fresh/photo.jpg', 'fresh');

        touch($freshOrphan, now()->subDay()->getTimestamp());

        EloquentMedia::query()->create([
            'uuid'                  => $mediaUuid,
            'model_type'            => null,
            'model_id'              => null,
            'collection_name'       => 'listing-images',
            'name'                  => 'photo',
            'file_name'             => 'photo.jpg',
            'mime_type'             => 'image/jpeg',
            'disk'                  => 'public',
            'conversions_disk'      => 'public',
            'size'                  => 10,
            'manipulations'         => [],
            'custom_properties'     => [],
            'generated_conversions' => [],
            'responsive_images'     => [],
            'media_type'            => MediaType::IMAGE,
            'visibility'            => MediaVisibility::PUBLIC,
        ]);

        $exitCode       = Artisan::call('media:cleanup-orphans', [
            '--disk'  => ['public'],
            '--days'  => 7,
            '--force' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($referencedFile);
        $this->assertFileDoesNotExist($orphanFile);
        $this->assertFileExists($freshOrphan);
    }

    private function putOldFile(string $relativePath, string $contents): string
    {
        $path = $this->putFile($relativePath, $contents);

        touch($path, now()->subDays(10)->getTimestamp());

        return $path;
    }

    private function putFile(string $relativePath, string $contents): string
    {
        $path = $this->diskRoot . '/' . $relativePath;

        File::ensureDirectoryExists((string) dirname($path));
        File::put($path, $contents);

        return $path;
    }
}
