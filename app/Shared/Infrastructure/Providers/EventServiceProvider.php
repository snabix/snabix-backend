<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Auth\Application\Listeners\SendEmailVerificationNotification;
use App\Auth\Domain\Events\UserRegistered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRegistered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];
}
