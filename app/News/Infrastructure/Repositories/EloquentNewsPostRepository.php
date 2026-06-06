<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Repositories;

use App\News\Domain\Contracts\NewsPostRepositoryInterface;
use App\News\Domain\Enums\NewsPostStatus;
use App\News\Infrastructure\Models\EloquentNewsPost;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentNewsPostRepository implements NewsPostRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, EloquentNewsPost>
     */
    public function listPublished(
        int $page = 1,
        int $perPage = 12,
        ?string $category = null,
        bool $featuredOnly = false,
    ): LengthAwarePaginator {
        return EloquentNewsPost::query()
            ->with(['coverMedia', 'authorAdmin'])
            ->where('status', NewsPostStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($category !== null && $category !== '', fn($query) => $query->where('category', $category))
            ->when($featuredOnly, fn($query) => $query->where('is_featured', true))
            ->latest('published_at')
            ->paginate(
                perPage: $perPage,
                pageName: 'page',
                page: $page,
            );
    }

    public function findPublishedBySlug(string $slug): ?EloquentNewsPost
    {
        return EloquentNewsPost::query()
            ->with(['coverMedia', 'authorAdmin', 'blocks.media'])
            ->where('slug', $slug)
            ->where('status', NewsPostStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->first();
    }
}
