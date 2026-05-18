<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationPayloadMapper
{
    /**
     * @template TValue
     *
     * @param  LengthAwarePaginator<int, TValue> $paginator
     * @return array<string, int>
     */
    public function map(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'perPage'     => $paginator->perPage(),
            'total'       => $paginator->total(),
            'lastPage'    => $paginator->lastPage(),
            'from'        => $paginator->firstItem() ?? 0,
            'to'          => $paginator->lastItem() ?? 0,
        ];
    }
}
