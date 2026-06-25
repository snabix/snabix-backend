<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Bot\Infrastructure\Middleware\EnsureBotServiceToken;
use App\Shared\Infrastructure\Middleware\LogRequestActivity;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->appendToGroup('api', LogRequestActivity::class);
        $middleware->alias([
            'bot.service' => EnsureBotServiceToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request): ?JsonResponse {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message'        => 'Сессия истекла или пользователь не авторизован.',
                'code'           => 'auth.unauthenticated',
                'sessionExpired' => true,
            ], 401);
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request): ?JsonResponse {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message'        => 'CSRF-токен устарел. Обновите сессию и повторите вход.',
                'code'           => 'auth.csrf-token-mismatch',
                'sessionExpired' => true,
            ], 419);
        });

        $exceptions->render(function (NotFoundException $exception, Request $request): JsonResponse | string {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 404);
            }

            return $exception->getMessage();
        });
    })->create();
