<?php

declare(strict_types=1);

namespace App\News\Application\UseCases\ListPublishedNewsPosts;

use App\Listing\Application\Support\PaginationPayloadMapper;
use App\News\Application\Support\NewsPostPayloadMapper;
use App\News\Domain\Contracts\NewsPostRepositoryInterface;
use App\News\Infrastructure\Models\EloquentNewsPost;

readonly class ListPublishedNewsPostsHandler
{
    public function __construct(
        private NewsPostRepositoryInterface $newsPostRepository,
        private NewsPostPayloadMapper $newsPostPayloadMapper,
        private PaginationPayloadMapper $paginationPayloadMapper,
    ) {}

    public function execute(ListPublishedNewsPostsInput $input): ListPublishedNewsPostsOutput
    {
        $paginator = $this->newsPostRepository->listPublished(
            page: $input->page,
            perPage: $input->perPage,
            category: $input->category,
            featuredOnly: $input->featuredOnly,
        );

        return ListPublishedNewsPostsOutput::from([
            'items' => $paginator
                ->getCollection()
                ->map(fn(EloquentNewsPost $post): array => $this->newsPostPayloadMapper->map($post))
                ->values()
                ->all(),
            'meta'  => $this->paginationPayloadMapper->map($paginator),
        ]);
    }
}
