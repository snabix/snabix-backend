<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Models;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Shared\Domain\Enums\SystemLogLevel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property SystemLogLevel $level
 * @property string $category
 * @property string|null $action
 * @property string $message
 * @property array<string, mixed>|null $context
 * @property string|null $route_name
 * @property string|null $method
 * @property string|null $path
 * @property int|null $status_code
 * @property int|null $duration_ms
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read EloquentUser|null $user
 * @property-read string|null $short_context
 */
class EloquentSystemLog extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $table = 'system_logs';

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'level',
        'category',
        'action',
        'message',
        'context',
        'route_name',
        'method',
        'path',
        'status_code',
        'duration_ms',
        'ip_address',
        'user_agent',
        'user_id',
    ];

    /**
     * @return BelongsTo<EloquentUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => SystemLogLevel::class,
            'context' => 'array',
            'status_code' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    protected function shortContext(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! is_array($this->context) || $this->context === []) {
                return null;
            }

            $json = json_encode($this->context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return is_string($json)
                ? Str::limit($json, 140)
                : null;
        });
    }
}
