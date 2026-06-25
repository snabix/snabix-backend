<?php

declare(strict_types=1);

namespace App\Bot\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureBotServiceToken
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.snabix_bot.service_token');
        $requestToken    = $request->bearerToken();

        if (
            ! is_string($configuredToken)
            || $configuredToken === ''
            || ! is_string($requestToken)
            || ! hash_equals($configuredToken, $requestToken)
        ) {
            return new JsonResponse([
                'message' => 'Bot service token is invalid.',
                'code'    => 'bot.unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
