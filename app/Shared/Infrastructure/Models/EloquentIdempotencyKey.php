<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string      $id
 * @property string      $scope
 * @property string      $actor_key_hash
 * @property string      $idempotency_key_hash
 * @property string      $request_fingerprint
 * @property string|null $resource_id
 * @property Carbon      $expires_at
 */
final class EloquentIdempotencyKey extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $table     = 'idempotency_keys';

    protected $keyType   = 'string';

    /** @var list<string> */
    protected $fillable  = [
        'scope',
        'actor_key_hash',
        'idempotency_key_hash',
        'request_fingerprint',
        'resource_id',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
