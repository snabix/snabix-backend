<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Auth\Application\Listeners\SendEmailVerificationNotification;
use App\Auth\Domain\Events\AuthenticationFailed;
use App\Auth\Domain\Events\PasswordResetCompleted;
use App\Auth\Domain\Events\PasswordResetRequested;
use App\Auth\Domain\Events\UserAvatarDeleted;
use App\Auth\Domain\Events\UserAvatarUpdated;
use App\Auth\Domain\Events\UserEmailVerificationRequested;
use App\Auth\Domain\Events\UserEmailVerified;
use App\Auth\Domain\Events\UserLoggedOut;
use App\Auth\Domain\Events\UserPasswordChanged;
use App\Auth\Domain\Events\UserProfileUpdated;
use App\Auth\Domain\Events\UserRegistered;
use App\Auth\Domain\Events\UserSignedIn;
use App\Listing\Domain\Events\ListingCreated;
use App\Listing\Domain\Events\ListingDeleted;
use App\Listing\Domain\Events\ListingSubmittedForReview;
use App\Shared\Infrastructure\Listeners\PersistLoggableEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRegistered::class                 => [
            SendEmailVerificationNotification::class,
            PersistLoggableEventListener::class,
        ],
        UserEmailVerificationRequested::class => [
            SendEmailVerificationNotification::class,
            PersistLoggableEventListener::class,
        ],
        UserSignedIn::class                   => [
            PersistLoggableEventListener::class,
        ],
        AuthenticationFailed::class           => [
            PersistLoggableEventListener::class,
        ],
        UserLoggedOut::class                  => [
            PersistLoggableEventListener::class,
        ],
        UserEmailVerified::class              => [
            PersistLoggableEventListener::class,
        ],
        PasswordResetRequested::class         => [
            PersistLoggableEventListener::class,
        ],
        PasswordResetCompleted::class         => [
            PersistLoggableEventListener::class,
        ],
        UserPasswordChanged::class            => [
            PersistLoggableEventListener::class,
        ],
        UserProfileUpdated::class             => [
            PersistLoggableEventListener::class,
        ],
        UserAvatarUpdated::class              => [
            PersistLoggableEventListener::class,
        ],
        UserAvatarDeleted::class              => [
            PersistLoggableEventListener::class,
        ],
        ListingCreated::class                 => [
            PersistLoggableEventListener::class,
        ],
        ListingSubmittedForReview::class      => [
            PersistLoggableEventListener::class,
        ],
        ListingDeleted::class                 => [
            PersistLoggableEventListener::class,
        ],
    ];
}
