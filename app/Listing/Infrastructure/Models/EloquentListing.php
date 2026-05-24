<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Models;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Media\Infrastructure\Models\EloquentMedia;
use Database\Factories\EloquentListingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property string           $id
 * @property string           $user_id
 * @property int              $category_id
 * @property ListingType      $type
 * @property ListingStatus    $status
 * @property ListingCondition $condition
 * @property string           $title
 * @property string           $slug
 * @property string           $description
 * @property int|null         $price
 * @property string           $currency
 * @property bool             $is_negotiable
 * @property string|null      $contact_name
 * @property string|null      $contact_phone
 * @property string|null      $contact_email
 * @property int              $views_count
 * @property bool             $is_featured
 * @property string|null      $rejection_reason
 * @property Carbon|null      $published_at
 * @property Carbon|null      $expires_at
 */
class EloquentListing extends Model
{
    /** @use HasFactory<EloquentListingFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $table     = 'listings';

    protected $keyType   = 'string';

    /** @var list<string> */
    protected $fillable  = [
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
     * @return HasMany<EloquentListingAttributeValue, $this>
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(EloquentListingAttributeValue::class, 'listing_id');
    }

    /**
     * @return MorphMany<EloquentMedia, $this>
     */
    public function media(): MorphMany
    {
        return $this
            ->morphMany(EloquentMedia::class, 'model')
            ->orderBy('order_column')
            ->orderBy('id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'          => ListingType::class,
            'status'        => ListingStatus::class,
            'condition'     => ListingCondition::class,
            'price'         => 'integer',
            'is_negotiable' => 'boolean',
            'views_count'   => 'integer',
            'is_featured'   => 'boolean',
            'published_at'  => 'datetime',
            'expires_at'    => 'datetime',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }
}
