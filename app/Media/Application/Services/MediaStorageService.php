<?php

declare(strict_types=1);

namespace App\Media\Application\Services;

use App\Media\Application\Data\PreparedMediaFile;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

readonly class MediaStorageService
{
    public function __construct(
        private MediaAttributesFactory $attributes,
        private MediaStorageOperationManager $operations,
    ) {}

    /**
     * @param  array<string, mixed> $attributes
     * @throws Throwable
     */
    public function createFromStoredUpload(string $sourceDisk, string $sourcePath, array $attributes): EloquentMedia
    {
        $source      = $this->operations->inspect($sourceDisk, $sourcePath);
        $media       = new EloquentMedia();
        $media->uuid = (string) Str::uuid();
        $media->forceFill($this->attributes->forCreate($source, $attributes));
        $prepared    = $this->operations->prepare($source, $media);

        try {
            $created = DB::transaction(function () use ($media, $prepared): EloquentMedia {
                $media->storage_key = $prepared->permanent->path;
                $media->save();

                DB::afterCommit(function () use ($prepared): void {
                    $this->operations->cleanup([
                        $prepared->source->object(),
                        $prepared->staged->object(),
                    ]);
                });

                return $media;
            });
        } catch (Throwable $exception) {
            $this->operations->cleanupPrepared($prepared);

            throw $exception;
        }

        return $created->refresh();
    }

    /**
     * @param  array<string, mixed> $attributes
     * @throws Throwable
     */
    public function replaceFromStoredUpload(
        EloquentMedia $media,
        string $sourceDisk,
        string $sourcePath,
        array $attributes,
    ): EloquentMedia {
        $source      = $this->operations->inspect($sourceDisk, $sourcePath);
        $oldObjects  = $this->operations->mediaObjects($media);
        $snapshot    = $this->operations->snapshot($media);
        $replacement = $this->attributes->forReplacement($media, $source, $attributes);
        $candidate   = clone $media;
        $candidate->forceFill($replacement);
        $prepared    = $this->operations->prepare($source, $candidate);

        try {
            $updated = DB::transaction(function () use (
                $media,
                $snapshot,
                $replacement,
                $prepared,
                $oldObjects,
            ): EloquentMedia {
                $current = $this->lockedCurrent($media);
                $this->operations->assertSnapshot($current, $snapshot);
                $current->forceFill([
                    ...$replacement,
                    'storage_key' => $prepared->permanent->path,
                ])->saveQuietly();

                $this->cleanupReplacementAfterCommit($prepared, $oldObjects);

                return $current;
            });
        } catch (Throwable $exception) {
            $this->operations->cleanupPrepared($prepared);

            throw $exception;
        }

        return $updated->refresh();
    }

    /**
     * @param  array<string, mixed> $attributes
     * @throws Throwable
     */
    public function updateMetadata(EloquentMedia $media, array $attributes): EloquentMedia
    {
        $attributes = $this->attributes->normalizeMetadata($attributes);
        $candidate  = clone $media;
        $candidate->forceFill($attributes);
        $snapshot   = $this->operations->snapshot($media);

        if (! $this->attributes->locationChanged($media, $candidate)) {
            return DB::transaction(function () use ($media, $snapshot, $attributes): EloquentMedia {
                $current = $this->lockedCurrent($media);
                $this->operations->assertSnapshot($current, $snapshot);
                $current->forceFill($attributes)->saveQuietly();

                return $current->refresh();
            });
        }

        return $this->moveForMetadataUpdate($media, $candidate, $snapshot, $attributes);
    }

    /**
     * @param array{disk: mixed, file_name: mixed, collection_name: mixed, media_type: mixed, storage_key: mixed} $snapshot
     * @param array<string, mixed>                                                                                $attributes
     *
     * @throws Throwable
     */
    private function moveForMetadataUpdate(
        EloquentMedia $media,
        EloquentMedia $candidate,
        array $snapshot,
        array $attributes,
    ): EloquentMedia {
        $source     = $this->operations->inspectMedia($media);
        $oldObjects = $this->operations->mediaObjects($media);
        $prepared   = $this->operations->prepare($source, $candidate);

        try {
            $updated = DB::transaction(function () use (
                $media,
                $snapshot,
                $attributes,
                $prepared,
                $oldObjects,
            ): EloquentMedia {
                $current = $this->lockedCurrent($media);
                $this->operations->assertSnapshot($current, $snapshot);
                $current->forceFill([
                    ...$attributes,
                    'storage_key'           => $prepared->permanent->path,
                    'generated_conversions' => [],
                    'responsive_images'     => [],
                ])->saveQuietly();

                DB::afterCommit(function () use ($prepared, $oldObjects): void {
                    $this->operations->cleanup([
                        $prepared->staged->object(),
                        ...$oldObjects,
                    ]);
                });

                return $current;
            });
        } catch (Throwable $exception) {
            $this->operations->cleanupPrepared($prepared);

            throw $exception;
        }

        return $updated->refresh();
    }

    /**
     * @param list<\App\Media\Application\Data\MediaStorageObject> $oldObjects
     */
    private function cleanupReplacementAfterCommit(PreparedMediaFile $prepared, array $oldObjects): void
    {
        DB::afterCommit(function () use ($prepared, $oldObjects): void {
            $this->operations->cleanup([
                $prepared->source->object(),
                $prepared->staged->object(),
                ...$oldObjects,
            ]);
        });
    }

    private function lockedCurrent(EloquentMedia $media): EloquentMedia
    {
        return EloquentMedia::query()
            ->whereKey($media->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }
}
