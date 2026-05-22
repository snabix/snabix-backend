<?php

declare(strict_types=1);

namespace App\Auth\Http\ReplaceProfileAddresses;

use App\Auth\Application\Services\UserAddressService;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReplaceProfileAddressesController
{
    /**
     * @throws ValidationException|Throwable
     */
    public function __invoke(
        ReplaceProfileAddressesRequest $request,
        UserAddressService $userAddressService,
    ): ReplaceProfileAddressesResponse {
        return ReplaceProfileAddressesResponse::make(
            $userAddressService->replace(
                $request->userId(),
                $request->addresses(),
            ),
        );
    }
}
