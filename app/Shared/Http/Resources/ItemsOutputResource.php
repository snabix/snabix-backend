<?php

declare(strict_types=1);

namespace App\Shared\Http\Resources;

use Illuminate\Http\Request;
use UnexpectedValueException;

abstract class ItemsOutputResource extends OutputResource
{
    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        $items   = parent::toArray($request)['items'] ?? null;

        if (! is_array($items) || ! array_is_list($items)) {
            throw new UnexpectedValueException(sprintf(
                '%s expects a list in the output items property.',
                static::class,
            ));
        }

        $objects = [];

        foreach ($items as $item) {
            $objects[] = $this->requireObjectArray($item, 'items');
        }

        return $objects;
    }
}
