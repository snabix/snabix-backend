<?php

declare(strict_types=1);

namespace App\Catalog\Application\Support;

use App\Catalog\Domain\Enums\CategoryImportAction;
use InvalidArgumentException;

readonly class CategoryImportChange
{
    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>      $after
     */
    public function __construct(
        public CategoryImportAction $action,
        public string $externalId,
        public int $depth,
        public ?array $before,
        public array $after,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $actionValue = $payload['action'] ?? null;
        $action      = is_string($actionValue)
            ? CategoryImportAction::tryFrom($actionValue)
            : null;
        $externalId  = $payload['externalId'] ?? null;
        $depth       = $payload['depth'] ?? null;
        $beforeValue = $payload['before'] ?? null;
        $before      = $beforeValue === null ? null : self::normalizeState($beforeValue);
        $after       = self::normalizeState($payload['after'] ?? null);

        if (
            $action === null
            || ! is_string($externalId)
            || ! is_int($depth)
            || ($beforeValue !== null && $before === null)
            || $after === null
        ) {
            throw new InvalidArgumentException('Category import manifest contains an invalid change.');
        }

        return new self(
            action: $action,
            externalId: $externalId,
            depth: $depth,
            before: $before,
            after: $after,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function normalizeState(mixed $state): ?array
    {
        if (! is_array($state)) {
            return null;
        }

        $normalized = [];

        foreach ($state as $key => $value) {
            if (! is_string($key)) {
                return null;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * @return array{
     *     action: string,
     *     externalId: string,
     *     depth: int,
     *     before: array<string, mixed>|null,
     *     after: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'action'     => $this->action->value,
            'externalId' => $this->externalId,
            'depth'      => $this->depth,
            'before'     => $this->before,
            'after'      => $this->after,
        ];
    }
}
