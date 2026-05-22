<?php

declare(strict_types=1);

namespace App\Auth\Http\ListProfileAddresses;

use App\Auth\Application\Services\UserAddressService;

class ListProfileAddressesController
{
    public function __invoke(
        ListProfileAddressesRequest $request,
        UserAddressService $userAddressService,
    ): ListProfileAddressesResponse {
        return ListProfileAddressesResponse::make($userAddressService->listPayload($request->userId()));
    }
}
