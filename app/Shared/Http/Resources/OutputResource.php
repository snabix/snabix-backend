<?php

declare(strict_types=1);

namespace App\Shared\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LogicException;
use Spatie\LaravelData\Contracts\TransformableData;
use UnexpectedValueException;

abstract class OutputResource extends JsonResource
{
    /**
     * @return array<array-key, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource instanceof TransformableData) {
            throw new LogicException(sprintf(
                '%s expects a Spatie transformable data object.',
                static::class,
            ));
        }

        return $this->resource->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    final protected function requireObjectArray(mixed $value, string $property): array
    {
        if (! is_array($value)) {
            throw new UnexpectedValueException(sprintf(
                '%s expects an array in the output %s property.',
                static::class,
                $property,
            ));
        }

        $object = [];

        foreach ($value as $key => $item) {
            if (! is_string($key)) {
                throw new UnexpectedValueException(sprintf(
                    '%s expects string keys in the output %s property.',
                    static::class,
                    $property,
                ));
            }

            $object[$key] = $item;
        }

        return $object;
    }
}
