<?php

declare(strict_types=1);

namespace App\Bot\Http;

use App\Bot\Application\BotStatsService;
use Illuminate\Http\JsonResponse;

final readonly class BotServiceController
{
    public function health(): JsonResponse
    {
        return response()->json(['data' => [
            'ok'      => true,
            'status'  => 200,
            'message' => 'Snabix backend service API is available.',
        ]]);
    }

    public function me(): JsonResponse
    {
        return response()->json(['data' => [
            'service' => 'snabix-bot',
            'mode'    => 'service',
            'version' => config('app.version', 'dev'),
        ]]);
    }

    public function stats(BotStatsService $statsService): JsonResponse
    {
        return response()->json(['data' => $statsService->summary()]);
    }
}
