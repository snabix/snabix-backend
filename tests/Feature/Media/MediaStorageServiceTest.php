<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use App\Media\Application\Contracts\MediaFileStorage;
use App\Media\Application\Jobs\CleanupMediaStorageObjectsJob;
use App\Media\Application\Services\MediaStorageCleaner;
use App\Media\Application\Services\MediaStorageReferenceChecker;
use App\Media\Application\Services\MediaStorageService;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Filament\Resources\Media\Schemas\MediaForm;
use App\Media\Infrastructure\Models\EloquentMedia;
use App\Media\Infrastructure\Storage\LaravelMediaFileStorage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FaultInjectingMediaFileStorage;
use Throwable;

class MediaStorageServiceTest extends FeatureTestCase
{
    public function test_media_file_can_be_created_from_stored_upload(): void
    {
        $this->fakeMediaDisks();
        Storage::disk('local')->put('filament-media-temp/photo.jpg', 'image-content');

        $media = $this->service()->createFromStoredUpload('local', 'filament-media-temp/photo.jpg', [
            'name'            => 'Главное фото',
            'collection_name' => 'listing_images',
            'media_type'      => MediaType::IMAGE,
            'visibility'      => MediaVisibility::PUBLIC,
            'disk'            => 'public',
            'description'     => 'Фото объявления',
        ]);

        $this->assertDatabaseHas('media', [
            'id'              => $media->id,
            'name'            => 'Главное фото',
            'collection_name' => 'listing_images',
            'media_type'      => MediaType::IMAGE->value,
            'visibility'      => MediaVisibility::PUBLIC->value,
            'disk'            => 'public',
            'storage_key'     => $media->storage_key,
            'description'     => 'Фото объявления',
        ]);
        $this->assertStringContainsString('/versions/', $this->storedPath($media));
        Storage::disk('public')->assertExists($this->storedPath($media));
        Storage::disk('local')->assertMissing('filament-media-temp/photo.jpg');
        $this->assertSame([], Storage::disk('local')->allFiles('media-staging'));
    }

    public function test_media_file_can_be_replaced_after_new_object_is_committed(): void
    {
        $this->fakeMediaDisks();
        $media   = $this->createImageMedia();
        $oldPath = $this->storedPath($media);
        Storage::disk('local')->put('filament-media-temp/document.pdf', 'new-document-content');

        $this->service()->replaceFromStoredUpload($media, 'local', 'filament-media-temp/document.pdf', [
            'media_type' => MediaType::DOCUMENT,
            'visibility' => MediaVisibility::PUBLIC,
            'disk'       => 'public',
        ]);

        $media->refresh();

        $this->assertSame('document.pdf', $media->file_name);
        $this->assertSame(MediaType::DOCUMENT, $media->media_type);
        $this->assertNotSame($oldPath, $this->storedPath($media));
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($this->storedPath($media));
        Storage::disk('local')->assertMissing('filament-media-temp/document.pdf');
    }

    public function test_existing_media_file_is_moved_when_directory_affecting_metadata_changes(): void
    {
        $this->fakeMediaDisks();
        $media   = $this->createImageMedia();
        $oldPath = $this->storedPath($media);

        $this->service()->updateMetadata($media, [
            'media_type' => MediaType::DOCUMENT,
            'visibility' => MediaVisibility::PUBLIC,
            'disk'       => 'public',
        ]);

        $media->refresh();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($this->storedPath($media));
        $this->assertStringStartsWith('documents/listing-images/', $this->storedPath($media));
    }

    public function test_partial_create_copy_is_compensated_and_source_upload_is_preserved(): void
    {
        $this->fakeMediaDisks();
        Storage::disk('local')->put('filament-media-temp/photo.jpg', 'image-content');
        $this->bindFaultingStorage(failAfterCopyCall: 2);

        try {
            $this->service()->createFromStoredUpload('local', 'filament-media-temp/photo.jpg', [
                'name'            => 'Главное фото',
                'collection_name' => 'listing_images',
                'media_type'      => MediaType::IMAGE,
                'visibility'      => MediaVisibility::PUBLIC,
                'disk'            => 'public',
            ]);
            $this->fail('Injected copy failure was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Injected media copy failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('media', 0);
        Storage::disk('local')->assertExists('filament-media-temp/photo.jpg');
        $this->assertSame([], Storage::disk('local')->allFiles('media-staging'));
        $this->assertSame([], Storage::disk('public')->allFiles('images'));
    }

    public function test_replace_database_failure_keeps_old_file_and_cleans_new_objects(): void
    {
        $this->fakeMediaDisks();
        $media             = $this->createImageMedia();
        $oldPath           = $this->storedPath($media);
        Storage::disk('local')->put('filament-media-temp/document.pdf', 'new-document-content');
        $databaseException = null;

        try {
            $this->service()->replaceFromStoredUpload($media, 'local', 'filament-media-temp/document.pdf', [
                'media_type'           => MediaType::DOCUMENT,
                'visibility'           => MediaVisibility::PUBLIC,
                'disk'                 => 'public',
                'uploaded_by_admin_id' => 999999,
            ]);
        } catch (Throwable $exception) {
            $databaseException = $exception;
        }

        $this->assertNotNull($databaseException, 'Foreign key violation was not thrown.');
        $media->refresh();

        $this->assertSame('photo.jpg', $media->file_name);
        $this->assertSame($oldPath, $this->storedPath($media));
        Storage::disk('public')->assertExists($oldPath);
        Storage::disk('local')->assertExists('filament-media-temp/document.pdf');
        $this->assertSame([$oldPath], Storage::disk('public')->allFiles('images'));
        $this->assertSame([], Storage::disk('local')->allFiles('media-staging'));
    }

    public function test_partial_move_copy_keeps_old_database_state_and_file(): void
    {
        $this->fakeMediaDisks();
        $media   = $this->createImageMedia();
        $oldPath = $this->storedPath($media);
        $this->bindFaultingStorage(failAfterCopyCall: 2);

        try {
            $this->service()->updateMetadata($media, [
                'media_type' => MediaType::DOCUMENT,
                'disk'       => 'public',
            ]);
            $this->fail('Injected copy failure was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Injected media copy failure.', $exception->getMessage());
        }

        $media->refresh();

        $this->assertSame(MediaType::IMAGE, $media->media_type);
        $this->assertSame($oldPath, $this->storedPath($media));
        Storage::disk('public')->assertExists($oldPath);
        $this->assertSame([$oldPath], Storage::disk('public')->allFiles('images'));
        $this->assertSame([], Storage::disk('local')->allFiles('media-staging'));
    }

    public function test_failed_after_commit_delete_is_queued_and_cleanup_is_idempotent(): void
    {
        $this->fakeMediaDisks();
        $media     = $this->createImageMedia();
        $oldPath   = $this->storedPath($media);
        Storage::disk('local')->put('filament-media-temp/document.pdf', 'new-document-content');
        Queue::fake();
        $this->bindFaultingStorage(deleteFailures: ['public:' . $oldPath]);

        $this->service()->replaceFromStoredUpload($media, 'local', 'filament-media-temp/document.pdf', [
            'media_type' => MediaType::DOCUMENT,
            'visibility' => MediaVisibility::PUBLIC,
            'disk'       => 'public',
        ]);

        $media->refresh();
        $newPath   = $this->storedPath($media);
        $queuedJob = null;

        Queue::assertPushedOn(
            'media-maintenance',
            CleanupMediaStorageObjectsJob::class,
            function (CleanupMediaStorageObjectsJob $job) use (&$queuedJob, $oldPath): bool {
                $queuedJob = $job;

                return $job->objects === [['disk' => 'public', 'path' => $oldPath]];
            },
        );
        $this->assertInstanceOf(CleanupMediaStorageObjectsJob::class, $queuedJob);
        Storage::disk('public')->assertExists($oldPath);
        Storage::disk('public')->assertExists($newPath);

        $cleaner   = new MediaStorageCleaner(
            new LaravelMediaFileStorage(),
            app(MediaStorageReferenceChecker::class),
        );
        $queuedJob->handle($cleaner);
        $queuedJob->handle($cleaner);

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_existing_media_upload_state_is_not_treated_as_new_upload_path(): void
    {
        $media = EloquentMedia::query()->create([
            'name'                  => 'Главное фото',
            'file_name'             => 'photo.jpg',
            'mime_type'             => 'image/jpeg',
            'disk'                  => 'public',
            'collection_name'       => 'listing_images',
            'size'                  => 128,
            'manipulations'         => [],
            'custom_properties'     => [],
            'generated_conversions' => [],
            'responsive_images'     => [],
            'media_type'            => MediaType::IMAGE,
            'visibility'            => MediaVisibility::PUBLIC,
        ]);

        $state = MediaForm::existingMediaState($media);

        $this->assertTrue(MediaForm::isExistingMediaState($state));
        $this->assertFalse(MediaForm::isExistingMediaState('filament-media-temp/photo.jpg'));
    }

    public function test_public_media_file_is_removed_from_storage_when_record_is_deleted(): void
    {
        $this->fakeMediaDisks();
        $media     = $this->createImageMedia();
        $mediaPath = $this->storedPath($media);

        Storage::disk('public')->assertExists($mediaPath);

        $media->delete();

        Storage::disk('public')->assertMissing($mediaPath);
    }

    private function fakeMediaDisks(): void
    {
        Storage::fake('local');
        Storage::fake('public');
    }

    private function createImageMedia(): EloquentMedia
    {
        Storage::disk('local')->put('filament-media-temp/photo.jpg', 'old-image-content');

        return $this->service()->createFromStoredUpload('local', 'filament-media-temp/photo.jpg', [
            'name'            => 'Главное фото',
            'collection_name' => 'listing_images',
            'media_type'      => MediaType::IMAGE,
            'visibility'      => MediaVisibility::PUBLIC,
            'disk'            => 'public',
        ]);
    }

    /**
     * @param list<string> $deleteFailures
     */
    private function bindFaultingStorage(?int $failAfterCopyCall = null, array $deleteFailures = []): void
    {
        $storage = new FaultInjectingMediaFileStorage(
            storage: new LaravelMediaFileStorage(),
            failAfterCopyCall: $failAfterCopyCall,
            deleteFailures: $deleteFailures,
        );

        $this->app->instance(MediaFileStorage::class, $storage);
    }

    private function service(): MediaStorageService
    {
        return app(MediaStorageService::class);
    }

    private function storedPath(EloquentMedia $media): string
    {
        $path = $media->storage_key;
        $this->assertIsString($path);
        $this->assertNotSame('', $path);

        return $path;
    }
}
