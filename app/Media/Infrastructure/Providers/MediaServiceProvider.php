<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Providers;

use App\Media\Application\Contracts\MediaFileStorage;
use App\Media\Infrastructure\Models\EloquentMedia;
use App\Media\Infrastructure\Policies\EloquentMediaPolicy;
use App\Media\Infrastructure\Storage\LaravelMediaFileStorage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MediaFileStorage::class, LaravelMediaFileStorage::class);
    }

    public function boot(): void
    {
        Gate::policy(EloquentMedia::class, EloquentMediaPolicy::class);
    }
}
