<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Services;

use App\Shared\Application\DTO\IdempotencyResult;
use App\Shared\Domain\Exceptions\IdempotencyConflictException;
use App\Shared\Infrastructure\Database\UniqueConstraintViolationDetector;
use App\Shared\Infrastructure\Models\EloquentIdempotencyKey;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use JsonException;
use RuntimeException;

final readonly class IdempotencyService
{
    private const string UNIQUE_CONSTRAINT = 'idempotency_keys_scope_actor_key_hash_unique';

    private const int MAX_CREATE_ATTEMPTS  = 2;

    public function __construct(
        private UniqueConstraintViolationDetector $uniqueConstraintViolationDetector,
    ) {}

    /**
     * @template TValue
     *
     * @param array<string, mixed>                 $payload
     * @param Closure(): IdempotencyResult<TValue> $operation
     * @param Closure(string): TValue              $replay
     *
     * @return TValue
     *
     * @throws JsonException
     * @throws UniqueConstraintViolationException
     */
    public function execute(
        ?string $idempotencyKey,
        string $scope,
        string $actorKey,
        array $payload,
        Closure $operation,
        Closure $replay,
    ): mixed {
        if ($idempotencyKey === null) {
            return DB::transaction(
                fn(): mixed => $operation()->value,
            );
        }

        $actorKeyHash       = $this->digest($actorKey);
        $idempotencyKeyHash = $this->digest($idempotencyKey);
        $requestFingerprint = $this->fingerprint($payload);

        for ($attempt = 0; $attempt < self::MAX_CREATE_ATTEMPTS; $attempt++) {
            try {
                return DB::transaction(function () use (
                    $scope,
                    $actorKeyHash,
                    $idempotencyKeyHash,
                    $requestFingerprint,
                    $operation,
                ): mixed {
                    $entry              = EloquentIdempotencyKey::query()->create([
                        'scope'                => $scope,
                        'actor_key_hash'       => $actorKeyHash,
                        'idempotency_key_hash' => $idempotencyKeyHash,
                        'request_fingerprint'  => $requestFingerprint,
                        'expires_at'           => now()->addHours($this->retentionHours()),
                    ]);

                    $result             = $operation();
                    $entry->resource_id = $result->resourceId;
                    $entry->save();

                    return $result->value;
                });
            } catch (UniqueConstraintViolationException $exception) {
                if (! $this->uniqueConstraintViolationDetector->matches($exception, self::UNIQUE_CONSTRAINT)) {
                    throw $exception;
                }

                $entry = EloquentIdempotencyKey::query()
                    ->where('scope', $scope)
                    ->where('actor_key_hash', $actorKeyHash)
                    ->where('idempotency_key_hash', $idempotencyKeyHash)
                    ->first();

                if (! $entry instanceof EloquentIdempotencyKey) {
                    continue;
                }

                if ($entry->expires_at->isPast()) {
                    EloquentIdempotencyKey::query()
                        ->whereKey($entry->id)
                        ->where('expires_at', '<=', now())
                        ->delete();

                    continue;
                }

                if (
                    ! hash_equals($entry->request_fingerprint, $requestFingerprint)
                    || $entry->resource_id === null
                ) {
                    throw new IdempotencyConflictException();
                }

                return $replay($entry->resource_id);
            }
        }

        throw new IdempotencyConflictException();
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @throws JsonException
     */
    private function fingerprint(array $payload): string
    {
        return $this->digest(json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
    }

    private function digest(string $value): string
    {
        $key = config('app.key');

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('APP_KEY is required for request idempotency.');
        }

        return hash_hmac('sha256', $value, $key);
    }

    private function retentionHours(): int
    {
        $hours = config('idempotency.retention_hours', 24);

        return is_int($hours) && $hours > 0 ? $hours : 24;
    }
}
