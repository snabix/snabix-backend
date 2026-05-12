<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Services;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Shared\Domain\Contracts\SessionAuthenticatorInterface;
use Illuminate\Support\Facades\Auth;

class SessionAuthenticatorService implements SessionAuthenticatorInterface
{
    public function login(string $userId): void
    {
        $user = EloquentUser::query()->find($userId);

        if (! $user) {
            return;
        }

        Auth::guard('web')->login($user);
        request()->session()->regenerate();
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
