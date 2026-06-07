<?php

declare(strict_types=1);

namespace Tests\Feature\News;

use App\News\Domain\Enums\NewsPostBlockType;
use App\News\Domain\Enums\NewsPostStatus;
use App\News\Infrastructure\Models\EloquentNewsPost;
use Tests\Feature\FeatureTestCase;

class NewsPostApiTest extends FeatureTestCase
{
    public function test_published_news_posts_can_be_listed(): void
    {
        EloquentNewsPost::factory()->create([
            'title'        => 'Опубликованная новость',
            'slug'         => 'published-news',
            'category'     => 'Новости',
            'status'       => NewsPostStatus::PUBLISHED,
            'published_at' => now()->subHour(),
        ]);
        EloquentNewsPost::factory()->draft()->create([
            'title' => 'Черновик новости',
            'slug'  => 'draft-news',
        ]);

        $this
            ->getJson('/api/v1/news')
            ->assertOk()
            ->assertJsonPath('data.items.0.title', 'Опубликованная новость')
            ->assertJsonMissingPath('data.items.0.contentBlocks')
            ->assertJsonMissing(['slug' => 'draft-news']);
    }

    public function test_published_news_post_detail_returns_content_blocks(): void
    {
        $post = EloquentNewsPost::factory()->create([
            'title'        => 'Конструктор новостей',
            'slug'         => 'news-builder',
            'status'       => NewsPostStatus::PUBLISHED,
            'published_at' => now()->subHour(),
            'views_count'  => 7,
        ]);

        $post->blocks()->createMany([
            [
                'type'       => NewsPostBlockType::LEAD,
                'sort_order' => 1,
                'data'       => ['text' => 'Лид материала'],
            ],
            [
                'type'       => NewsPostBlockType::TABLE,
                'sort_order' => 2,
                'data'       => [
                    'columns' => ['Поле', 'Значение'],
                    'rows'    => [['Статус', 'Опубликовано']],
                ],
            ],
        ]);

        $this
            ->getJson('/api/v1/news/news-builder')
            ->assertOk()
            ->assertJsonPath('data.slug', 'news-builder')
            ->assertJsonPath('data.contentBlocks.0.type', 'lead')
            ->assertJsonPath('data.contentBlocks.0.text', 'Лид материала')
            ->assertJsonPath('data.contentBlocks.1.type', 'table')
            ->assertJsonPath('data.contentBlocks.1.columns.0', 'Поле')
            ->assertJsonPath('data.viewsCount', 8);

        $this->assertDatabaseHas('news_posts', [
            'id'          => $post->id,
            'views_count' => 8,
        ]);
    }

    public function test_draft_news_post_is_not_publicly_visible(): void
    {
        EloquentNewsPost::factory()->draft()->create([
            'slug' => 'hidden-draft',
        ]);

        $this
            ->getJson('/api/v1/news/hidden-draft')
            ->assertNotFound();
    }
}
