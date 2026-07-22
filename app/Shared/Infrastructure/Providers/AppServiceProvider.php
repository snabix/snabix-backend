<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Mail\Application\Contracts\MailSender;
use App\Mail\Infrastructure\Services\LaravelMailSender;
use App\Shared\Application\Support\ReferenceDataCache;
use App\Shared\Domain\Contracts\HasherInterface;
use App\Shared\Domain\Contracts\SessionAuthenticatorInterface;
use App\Shared\Infrastructure\Models\EloquentSystemLog;
use App\Shared\Infrastructure\Policies\EloquentSystemLogPolicy;
use App\Shared\Infrastructure\Services\HasherService;
use App\Shared\Infrastructure\Services\SessionAuthenticatorService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ReferenceDataCache::class);

        $this->app->bind(
            HasherInterface::class,
            HasherService::class,
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
        $this->protectPersistentDatabase();

        CreateRecord::disableCreateAnother();

        Gate::policy(EloquentSystemLog::class, EloquentSystemLogPolicy::class);
    }

    private function protectPersistentDatabase(): void
    {
        $connection     = config('database.default');
        $isTestDatabase = $this->app->environment('testing')
            && $connection === 'pgsql'
            && config("database.connections.{$connection}.host") === 'db-test'
            && config("database.connections.{$connection}.database") === 'snabix_test';

        FreshCommand::prohibit(! $isTestDatabase);
        RefreshCommand::prohibit(! $isTestDatabase);
        ResetCommand::prohibit(! $isTestDatabase);
        RollbackCommand::prohibit(! $isTestDatabase);
        WipeCommand::prohibit(! $isTestDatabase);
    }
}
