<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Exceptions\NotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundException $exception, Request $request): JsonResponse | string {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 404);
            }

            return $exception->getMessage();
        });
    })->create();
