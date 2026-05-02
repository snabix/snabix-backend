<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\CLI\AuthCLIMakeAdminUser;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            AuthCLIMakeAdminUser::class,
        ]);
    }
}
