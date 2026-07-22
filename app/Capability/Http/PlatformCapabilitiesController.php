<?php

declare(strict_types=1);

namespace App\Capability\Http;

use App\Capability\Application\PlatformCapabilityService;
use Illuminate\Http\JsonResponse;

final readonly class PlatformCapabilitiesController
{
    public function __invoke(PlatformCapabilityService $capabilities): JsonResponse
    {
        return response()->json([
            'data' => $capabilities->contract(),
        ]);
    }
}
