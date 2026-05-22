<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Auth\Infrastructure\Policies\EloquentAdminPolicy;
use App\Auth\Infrastructure\Policies\EloquentUserPolicy;
use App\Auth\Infrastructure\Repositories\EloquentUserRepository;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Catalog\Infrastructure\Policies\EloquentCategoryAttributeDefinitionPolicy;
use App\Catalog\Infrastructure\Policies\EloquentCategoryPolicy;
use App\Mail\Application\Contracts\MailSender;
use App\Mail\Infrastructure\Services\LaravelMailSender;
use App\Media\Infrastructure\Models\EloquentMedia;
use App\Media\Infrastructure\Policies\EloquentMediaPolicy;
use App\Policies\RolePolicy;
use App\Shared\Domain\Contracts\HasherInterface;
use App\Shared\Domain\Contracts\SessionAuthenticatorInterface;
use App\Shared\Infrastructure\Models\EloquentSystemLog;
use App\Shared\Infrastructure\Policies\EloquentSystemLogPolicy;
use App\Shared\Infrastructure\Services\HasherService;
use App\Shared\Infrastructure\Services\SessionAuthenticatorService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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
        CreateRecord::disableCreateAnother();

        Gate::before(
            fn(mixed $user): ?bool => $user instanceof EloquentAdmin && $user->hasRole('super_admin')
                ? true
                : null,
        );

        Gate::policy(EloquentAdmin::class, EloquentAdminPolicy::class);
        Gate::policy(EloquentUser::class, EloquentUserPolicy::class);
        Gate::policy(EloquentCategory::class, EloquentCategoryPolicy::class);
        Gate::policy(EloquentCategoryAttributeDefinition::class, EloquentCategoryAttributeDefinitionPolicy::class);
        Gate::policy(EloquentMedia::class, EloquentMediaPolicy::class);
        Gate::policy(EloquentSystemLog::class, EloquentSystemLogPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
    }
}
