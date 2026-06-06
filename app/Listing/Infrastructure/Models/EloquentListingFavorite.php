<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Models;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int         $id
 * @property string      $user_id
 * @property string      $listing_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EloquentListingFavorite extends Model
{
    protected $table    = 'listing_favorites';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'listing_id',
    ];

    /**
     * @return BelongsTo<EloquentUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }

    /**
     * @return BelongsTo<EloquentListing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(EloquentListing::class, 'listing_id');
    }
}
