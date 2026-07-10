<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Models;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentRegion;
use App\Media\Infrastructure\Models\EloquentMedia;
use Database\Factories\EloquentListingFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property      string                         $id
 * @property      string                         $user_id
 * @property      string                         $category_id
 * @property      ListingType                    $type
 * @property      ListingStatus                  $status
 * @property      ListingCondition               $condition
 * @property      string                         $title
 * @property      string                         $slug
 * @property      string                         $description
 * @property      int|null                       $price
 * @property      string                         $currency
 * @property      bool                           $is_negotiable
 * @property      string|null                    $contact_name
 * @property      string|null                    $contact_phone
 * @property      string|null                    $contact_email
 * @property      string|null                    $profile_address_id
 * @property      int|null                       $region_id
 * @property      int|null                       $city_id
 * @property      array<string, mixed>|null      $address_snapshot
 * @property      int                            $views_count
 * @property      bool                           $is_featured
 * @property      string|null                    $rejection_reason
 * @property      Carbon|null                    $published_at
 * @property      Carbon|null                    $expires_at
 * @property-read Collection<int, EloquentMedia> $orderedMedia
 * @property-read EloquentRegion|null            $region
 * @property-read EloquentCity|null              $city
 */
class EloquentListing extends Model implements HasMedia
{
    /** @use HasFactory<EloquentListingFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithMedia;

    public const string MEDIA_CONVERSION_CARD    = 'listing-card';

    public const string MEDIA_CONVERSION_GALLERY = 'listing-gallery';

    public $incrementing                         = false;

    protected $table                             = 'listings';

    protected $keyType                           = 'string';

    /** @var list<string> */
    protected $fillable                          = [
        'id',
        'user_id',
        'category_id',
        'type',
        'status',
        'condition',
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'is_negotiable',
        'contact_name',
        'contact_phone',
        'contact_email',
        'profile_address_id',
        'region_id',
        'city_id',
        'address_snapshot',
        'views_count',
        'is_featured',
        'rejection_reason',
        'published_at',
        'expires_at',
    ];

    protected static function newFactory(): EloquentListingFactory
    {
        return EloquentListingFactory::new();
    }

    /**
     * @return BelongsTo<EloquentUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }

    /**
     * @return BelongsTo<EloquentCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id');
    }

    /**
     * @return BelongsTo<EloquentRegion, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(EloquentRegion::class, 'region_id');
    }

    /**
     * @return BelongsTo<EloquentCity, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(EloquentCity::class, 'city_id');
    }

    /**
     * @return HasMany<EloquentListingAttributeValue, $this>
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(EloquentListingAttributeValue::class, 'listing_id');
    }

    /**
     * @return HasMany<EloquentListingFavorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(EloquentListingFavorite::class, 'listing_id');
    }

    /**
     * @return MorphMany<EloquentMedia, $this>
     */
    public function orderedMedia(): MorphMany
    {
        return $this
            ->morphMany(EloquentMedia::class, 'model')
            ->where('collection_name', 'listing-images')
            ->orderBy('order_column')
            ->orderBy('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('listing-images')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion(self::MEDIA_CONVERSION_CARD)
            ->performOnCollections('listing-images')
            ->fit(Fit::Crop, 640, 480)
            ->format('webp');

        $this
            ->addMediaConversion(self::MEDIA_CONVERSION_GALLERY)
            ->performOnCollections('listing-images')
            ->fit(Fit::Max, 1600, 1200)
            ->format('webp');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'            => ListingType::class,
            'status'          => ListingStatus::class,
            'condition'       => ListingCondition::class,
            'price'           => 'integer',
            'is_negotiable'   => 'boolean',
            'views_count'     => 'integer',
            'is_featured'     => 'boolean',
            'region_id'       => 'integer',
            'city_id'         => 'integer',
            'address_snapshot'=> 'array',
            'published_at'    => 'datetime',
            'expires_at'      => 'datetime',
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
        ];
    }
}
