<?php

declare(strict_types=1);

namespace App\Review\Infrastructure\Models;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Review\Domain\Enums\UserReviewStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property      string           $id
 * @property      string           $reviewer_id
 * @property      string           $reviewee_id
 * @property      string           $listing_id
 * @property      int              $rating
 * @property      string|null      $comment
 * @property      UserReviewStatus $status
 * @property      Carbon|null      $published_at
 * @property-read EloquentUser     $reviewer
 * @property-read EloquentUser     $reviewee
 * @property-read EloquentListing  $listing
 */
class EloquentUserReview extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $table     = 'user_reviews';

    protected $keyType   = 'string';

    /** @var list<string> */
    protected $fillable  = [
        'id',
        'reviewer_id',
        'reviewee_id',
        'listing_id',
        'rating',
        'comment',
        'status',
        'published_at',
    ];

    /**
     * @return BelongsTo<EloquentUser, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'reviewer_id');
    }

    /**
     * @return BelongsTo<EloquentUser, $this>
     */
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'reviewee_id');
    }

    /**
     * @return BelongsTo<EloquentListing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(EloquentListing::class, 'listing_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating'       => 'integer',
            'status'       => UserReviewStatus::class,
            'published_at' => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }
}
