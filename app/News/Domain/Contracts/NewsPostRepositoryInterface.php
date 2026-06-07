<?php

declare(strict_types=1);

namespace App\News\Domain\Contracts;

use App\News\Infrastructure\Models\EloquentNewsPost;
use Illuminate\Pagination\LengthAwarePaginator;

interface NewsPostRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, EloquentNewsPost>
     */
    public function listPublished(
        int $page = 1,
        int $perPage = 12,
        ?string $category = null,
        bool $featuredOnly = false,
    ): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): ?EloquentNewsPost;
}
