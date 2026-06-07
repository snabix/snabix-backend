<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Models;

use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentRegion;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property      string            $id
 * @property      string            $user_id
 * @property      int               $region_id
 * @property      int|null          $city_id
 * @property      string|null       $label
 * @property      string|null       $address_line
 * @property      bool              $is_primary
 * @property      int               $sort_order
 * @property      Carbon            $created_at
 * @property      Carbon            $updated_at
 * @property-read EloquentUser      $user
 * @property-read EloquentRegion    $region
 * @property-read EloquentCity|null $city
 */
class EloquentUserAddress extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $table     = 'user_addresses';

    protected $keyType   = 'string';

    /** @var list<string> */
    protected $fillable  = [
        'id',
        'user_id',
        'region_id',
        'city_id',
        'label',
        'address_line',
        'is_primary',
        'sort_order',
    ];

    /**
     * @return BelongsTo<EloquentUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'region_id'  => 'integer',
            'city_id'    => 'integer',
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
