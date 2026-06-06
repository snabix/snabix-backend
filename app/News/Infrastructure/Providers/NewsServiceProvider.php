<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Providers;

use App\News\Application\Support\NewsPostPayloadMapper;
use App\News\Domain\Contracts\NewsPostRepositoryInterface;
use App\News\Infrastructure\Repositories\EloquentNewsPostRepository;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NewsPostRepositoryInterface::class,
            EloquentNewsPostRepository::class,
        );

        $this->app->singleton(NewsPostPayloadMapper::class);
    }
}
