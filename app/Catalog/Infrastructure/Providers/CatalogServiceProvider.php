<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Providers;

use App\Catalog\Application\Support\CategoryAttributeDefinitionPayloadMapper;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Catalog\Infrastructure\Policies\EloquentCategoryAttributeDefinitionPolicy;
use App\Catalog\Infrastructure\Policies\EloquentCategoryPolicy;
use App\Catalog\Infrastructure\Repositories\EloquentCategoryAttributeDefinitionRepository;
use App\Catalog\Infrastructure\Repositories\EloquentCategoryRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class,
        );

        $this->app->bind(
            CategoryAttributeDefinitionRepositoryInterface::class,
            EloquentCategoryAttributeDefinitionRepository::class,
        );

        $this->app->singleton(CategoryAttributeDefinitionPayloadMapper::class);
    }

    public function boot(): void
    {
        Gate::policy(EloquentCategory::class, EloquentCategoryPolicy::class);
        Gate::policy(EloquentCategoryAttributeDefinition::class, EloquentCategoryAttributeDefinitionPolicy::class);
    }
}
