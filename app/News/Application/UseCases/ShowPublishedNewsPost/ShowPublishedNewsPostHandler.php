<?php

declare(strict_types=1);

namespace App\News\Application\UseCases\ShowPublishedNewsPost;

use App\News\Application\Support\NewsPostPayloadMapper;
use App\News\Domain\Contracts\NewsPostRepositoryInterface;
use App\News\Infrastructure\Models\EloquentNewsPost;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class ShowPublishedNewsPostHandler
{
    public function __construct(
        private NewsPostRepositoryInterface $newsPostRepository,
        private NewsPostPayloadMapper $newsPostPayloadMapper,
    ) {}

    public function execute(ShowPublishedNewsPostInput $input): ShowPublishedNewsPostOutput
    {
        $post = $this->newsPostRepository->findPublishedBySlug($input->slug);

        if ($post === null) {
            throw (new ModelNotFoundException())->setModel(EloquentNewsPost::class, [$input->slug]);
        }

        $post->increment('views_count');
        $post->refresh();
        $post->loadMissing(['coverMedia', 'authorAdmin', 'blocks.blockMedia']);

        return ShowPublishedNewsPostOutput::from([
            'item' => $this->newsPostPayloadMapper->map($post, includeBlocks: true),
        ]);
    }
}
