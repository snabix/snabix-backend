<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Models;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Media\Application\Support\MediaTypeDetector;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property      int                $id
 * @property      string|null        $storage_key
 * @property      MediaType          $media_type
 * @property      MediaVisibility    $visibility
 * @property      int|null           $uploaded_by_admin_id
 * @property      string|null        $description
 * @property-read EloquentAdmin|null $uploadedByAdmin
 * @property-read string             $media_type_label
 * @property-read string             $visibility_label
 * @property-read string             $human_readable_size
 * @property-read bool               $is_previewable
 */
class EloquentMedia extends Media
{
    protected $appends = [
        'original_url',
        'preview_url',
        'media_type_label',
        'visibility_label',
        'human_readable_size',
        'is_previewable',
    ];

    protected $casts   = [
        'manipulations'         => 'array',
        'custom_properties'     => 'array',
        'generated_conversions' => 'array',
        'responsive_images'     => 'array',
        'media_type'            => MediaType::class,
        'visibility'            => MediaVisibility::class,
        'size'                  => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $media): void {
            $mediaType         = $media->getAttribute('media_type');

            if ($mediaType instanceof MediaType) {
                return;
            }

            $media->media_type = $media->detectedMediaType();
        });
    }

    /**
     * @return BelongsTo<EloquentAdmin, $this>
     */
    public function uploadedByAdmin(): BelongsTo
    {
        return $this->belongsTo(EloquentAdmin::class, 'uploaded_by_admin_id');
    }

    public function detectedMediaType(): MediaType
    {
        /** @var MediaTypeDetector $detector */
        $detector  = app(MediaTypeDetector::class);

        $extension = pathinfo($this->file_name, PATHINFO_EXTENSION) ?: null;

        return $detector->detect($this->mime_type, $extension);
    }

    /**
     * Spatie expects a related model for conversion discovery. Our media bank
     * intentionally allows unattached files, so unattached media has no model
     * conversions to resolve.
     *
     * @return array<int, string>
     */
    public function getMediaConversionNames(): array
    {
        if (blank($this->model_type)) {
            return [];
        }

        return parent::getMediaConversionNames();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function mediaTypeLabel(): Attribute
    {
        return Attribute::get(fn(): string => $this->media_type->label());
    }

    /**
     * @return Attribute<string, never>
     */
    protected function visibilityLabel(): Attribute
    {
        return Attribute::get(fn(): string => $this->visibility->label());
    }

    /**
     * @return Attribute<string, never>
     */
    protected function humanReadableSize(): Attribute
    {
        return Attribute::get(fn(): string => Number::fileSize($this->size));
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isPreviewable(): Attribute
    {
        return Attribute::get(
            fn(): bool => in_array($this->media_type, [MediaType::IMAGE, MediaType::VIDEO, MediaType::DOCUMENT], true),
        );
    }
}
