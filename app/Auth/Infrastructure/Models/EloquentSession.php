<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property      string      $id
 * @property      string|null $user_id
 * @property      string|null $ip_address
 * @property      string|null $user_agent
 * @property      string      $payload
 * @property      int         $last_activity
 * @property-read Carbon      $last_activity_at
 */
class EloquentSession extends Model
{
    public $incrementing = false;

    public $timestamps   = false;

    protected $table     = 'sessions';

    protected $keyType   = 'string';

    protected $guarded   = [];

    public function lastActivityAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->last_activity);
    }

    protected function casts(): array
    {
        return [
            'last_activity' => 'integer',
        ];
    }
}
