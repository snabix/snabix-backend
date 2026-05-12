<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Infrastructure\Repositories\EloquentUserRepository;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Repositories\EloquentCategoryRepository;
use App\Mail\Application\Contracts\MailSender;
use App\Mail\Infrastructure\Services\LaravelMailSender;
use App\Shared\Domain\Contracts\HasherInterface;
use App\Shared\Domain\Contracts\SessionAuthenticatorInterface;
use App\Shared\Domain\Contracts\TokenCreatorInterface;
use App\Shared\Infrastructure\Services\HasherService;
use App\Shared\Infrastructure\Services\SessionAuthenticatorService;
use App\Shared\Infrastructure\Services\SanctumTokenCreatorService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class,
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class,
        );

        $this->app->bind(
            HasherInterface::class,
            HasherService::class,
        );

        $this->app->bind(
            TokenCreatorInterface::class,
            SanctumTokenCreatorService::class,
        );

        $this->app->bind(
            SessionAuthenticatorInterface::class,
            SessionAuthenticatorService::class,
        );

        $this->app->bind(
            MailSender::class,
            LaravelMailSender::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
