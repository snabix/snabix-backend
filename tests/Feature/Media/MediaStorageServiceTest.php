<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use App\Media\Application\Services\MediaStorageService;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\FeatureTestCase;

class MediaStorageServiceTest extends FeatureTestCase
{
    public function test_media_file_can_be_created_from_stored_upload(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        Storage::disk('local')->put('filament-media-temp/photo.jpg', 'image-content');

        /** @var MediaStorageService $service */
        $service = app(MediaStorageService::class);

        $media   = $service->createFromStoredUpload('local', 'filament-media-temp/photo.jpg', [
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
            'description'     => 'Фото объявления',
        ]);

        Storage::disk('public')->assertExists($this->expectedMediaPath($media, MediaType::IMAGE, 'photo.jpg'));
        Storage::disk('local')->assertMissing('filament-media-temp/photo.jpg');
    }

    public function test_media_file_can_be_replaced_and_old_file_is_removed(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        Storage::disk('local')->put('filament-media-temp/photo.jpg', 'old-image-content');

        /** @var MediaStorageService $service */
        $service = app(MediaStorageService::class);

        $media   = $service->createFromStoredUpload('local', 'filament-media-temp/photo.jpg', [
            'name'            => 'Главное фото',
            'collection_name' => 'listing_images',
            'media_type'      => MediaType::IMAGE,
            'visibility'      => MediaVisibility::PUBLIC,
            'disk'            => 'public',
        ]);

        Storage::disk('local')->put('filament-media-temp/document.pdf', 'new-document-content');

        $service->replaceFromStoredUpload($media, 'local', 'filament-media-temp/document.pdf', [
            'media_type' => MediaType::DOCUMENT,
            'visibility' => MediaVisibility::PUBLIC,
            'disk'       => 'public',
        ]);

        $media->refresh();

        $this->assertSame('document.pdf', $media->file_name);
        $this->assertSame(MediaType::DOCUMENT, $media->media_type);
        Storage::disk('public')->assertMissing($this->expectedMediaPath($media, MediaType::IMAGE, 'photo.jpg'));
        Storage::disk('public')->assertExists($this->expectedMediaPath($media, MediaType::DOCUMENT, 'document.pdf'));
        Storage::disk('local')->assertMissing('filament-media-temp/document.pdf');
    }

    public function test_existing_media_file_is_moved_when_directory_affecting_metadata_changes(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        Storage::disk('local')->put('filament-media-temp/photo.jpg', 'image-content');

        /** @var MediaStorageService $service */
        $service = app(MediaStorageService::class);

        $media   = $service->createFromStoredUpload('local', 'filament-media-temp/photo.jpg', [
            'name'            => 'Главное фото',
            'collection_name' => 'listing_images',
            'media_type'      => MediaType::IMAGE,
            'visibility'      => MediaVisibility::PUBLIC,
            'disk'            => 'public',
        ]);

        $service->updateMetadata($media, [
            'media_type' => MediaType::DOCUMENT,
            'visibility' => MediaVisibility::PUBLIC,
            'disk'       => 'public',
        ]);

        Storage::disk('public')->assertMissing($this->expectedMediaPath($media, MediaType::IMAGE, 'photo.jpg'));
        Storage::disk('public')->assertExists($this->expectedMediaPath($media, MediaType::DOCUMENT, 'photo.jpg'));
    }

    public function test_public_media_file_is_removed_from_storage_when_record_is_deleted(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        Storage::disk('local')->put('filament-media-temp/photo.jpg', 'image-content');

        /** @var MediaStorageService $service */
        $service   = app(MediaStorageService::class);

        $media     = $service->createFromStoredUpload('local', 'filament-media-temp/photo.jpg', [
            'name'            => 'Главное фото',
            'collection_name' => 'listing_images',
            'media_type'      => MediaType::IMAGE,
            'visibility'      => MediaVisibility::PUBLIC,
            'disk'            => 'public',
        ]);

        $mediaPath = $this->expectedMediaPath($media, MediaType::IMAGE, 'photo.jpg');

        Storage::disk('public')->assertExists($mediaPath);

        $media->delete();

        Storage::disk('public')->assertMissing($mediaPath);
    }

    private function expectedMediaPath(EloquentMedia $media, MediaType $type, string $fileName): string
    {
        return $type->directory() . '/listing-images/' . $media->uuid . '/' . $fileName;
    }
}
